<?php
// API tư vấn sản phẩm dùng Gemini với logic tìm kiếm nâng cao
header('Content-Type: application/json; charset=utf-8');
session_start();

// Tắt hiển thị lỗi ra ngoài để tránh phá JSON trả về
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Kết nối tới database
require_once __DIR__ . '/../database/connect.php';

// Đọc nội dung JSON gửi lên
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];

// Câu hỏi từ người dùng
$query   = isset($input['message']) ? trim($input['message']) : '';
$newChat = !empty($input['new_chat']);

if ($newChat) {
    // Xóa ngữ cảnh trước đó khi bắt đầu cuộc trò chuyện mới
    unset($_SESSION['ai_last_products'], $_SESSION['ai_last_question']);
}

if ($query === '') {
    echo json_encode(['success' => false, 'reply' => '']);
    exit;
}

/**
 * Tạo chuỗi mô tả một sản phẩm bao gồm tên, giá, danh mục, specs và mô tả.
 * @param array $row Dòng dữ liệu của sản phẩm
 * @return string Chuỗi dạng markdown dùng trong prompt
 */
function build_product_info_line(array $row): string
{
    $name  = $row['name'] ?? '';
    $id    = isset($row['id']) ? (int)$row['id'] : 0;
    // Tạo đường dẫn tương đối tới trang sản phẩm
    $link  = "product_detail.php?id={$id}";

    // Tính giá ưu tiên sale_price, nếu không có thì dùng price
    $priceValue = 0.0;
    if (!empty($row['sale_price']) && (float)$row['sale_price'] > 0) {
        $priceValue = (float)$row['sale_price'];
    } elseif (!empty($row['price'])) {
        $priceValue = (float)$row['price'];
    }
    $price = number_format($priceValue, 0, ',', '.') . ' VND';

    $desc     = $row['description'] ?? '';
    $specs    = $row['specs'] ?? '';
    $category = $row['category'] ?? '';

    // Rút gọn specs nếu quá dài để tránh prompt quá lớn
    if (function_exists('mb_strlen')) {
        if (mb_strlen($specs) > 220) {
            $specs = mb_substr($specs, 0, 220) . '...';
        }
    } else {
        if (strlen($specs) > 220) {
            $specs = substr($specs, 0, 220) . '...';
        }
    }

    $out  = "✨ **[{$name}]({$link})** - {$price}\n";
    if ($category !== '') {
        $out .= "   • Danh mục: {$category}\n";
    }
    if ($specs !== '') {
        $out .= "   • Thông số chính: {$specs}\n";
    }
    if ($desc !== '') {
        $out .= "   • Mô tả: {$desc}\n";
    }
    $out .= "\n";

    return $out;
}

/* ============================================================
   Tiền xử lý câu hỏi để xây dựng điều kiện tìm kiếm
   ============================================================ */

// Chuẩn hóa về chữ thường (kể cả tiếng Việt có dấu)
$normalized = mb_strtolower($query, 'UTF-8');

// Danh sách từ khóa sẽ được tìm kiếm
$keywords = [];

// Danh sách từ dừng (sẽ bỏ qua khi tách từ khóa)
$stopWords = [
    'tìm', 'tim', 'kiếm', 'kiem', 'tìm kiếm', 'tim kiem', 'giới thiệu', 'gioi thieu',
    'giúp', 'giup', 'chọn', 'chon', 'cho', 'tôi', 'toi', 'với', 'voi', 'cần', 'can',
    'có', 'co', 'phù hợp', 'phu hop', 'phù', 'phu', 'hợp', 'hop',
    'nào', 'nao', 'muốn', 'muon', 'khoảng', 'khoang', 'tham khảo', 'tham khao',
    'dùng', 'dung', 'dành', 'danh', 'theo', 'mua', 'nhu cầu', 'nhu cau',
    'phần cứng', 'phan cung', 'thiết bị', 'thiet bi', 'sản phẩm', 'san pham',
    'xin', 'vui lòng', 'vui long', 'vui', 'mong', 'hãy', 'hay',
    'đến', 'den', 'hãy cho', 'hay cho', 'gởi', 'gui',
    // Stop words cho context để tránh lặp lại keyword
    'sinh', 'viên', 'sinh viên', 'hoc', 'sinh', 'học sinh',
    'văn', 'phòng', 'van', 'phong', 'office', 'nhân', 'viên', 'nhan', 'vien'
];

// Danh sách hãng cần nhận diện
$brandNames = ['msi', 'dell', 'acer', 'asus', 'lenovo', 'hp', 'gigabyte', 'apple', 'macbook'];

// Danh sách từ khóa chỉ ra linh kiện, để không tự thêm 'laptop'
$componentWords = [
    'ram', 'ssd', 'hdd', 'ổ cứng', 'o cung',
    'màn hình', 'man hinh', 'monitor',
    'chuột', 'chuot', 'mouse',
    'bàn phím', 'ban phim', 'keyboard',
    'tai nghe', 'headphone',
    'loa', 'speaker',
    'vga', 'card màn hình', 'card man hinh'
];

// Các ngữ cảnh mà mặc định ưu tiên laptop (khi không nêu rõ linh kiện)
$contextLaptopWords = [
    // Sinh viên / học sinh
    'sinh viên', 'sinhvien', 'học sinh', 'hoc sinh',
    // Văn phòng
    'văn phòng', 'van phong', 'office',
    'nhân viên văn phòng', 'nhan vien van phong',
    'kế toán', 'ke toan'
];

// Phát hiện hãng
$brand = '';
foreach ($brandNames as $b) {
    if (mb_stripos($normalized, $b, 0, 'UTF-8') !== false) {
        $brand = $b;
        break;
    }
}

// Phát hiện điều kiện giá (trên/dưới ... triệu)
$priceFilterType = '';
$priceFilterValue = null;

// Mẫu 'dưới' hoặc '<' hoặc '<='
if (preg_match('/(?:dưới|duoi|<|<=)\s*(\d+(?:[\.,]\d+)?)\s*(?:tr|triệu|trieu)?/u', $normalized, $m)) {
    $priceFilterType  = 'max';
    // Chuyển đổi số thành đơn vị triệu
    $priceFilterValue = (float)str_replace(',', '.', $m[1]) * 1000000;
} elseif (preg_match('/(?:trên|tren|>|>=)\s*(\d+(?:[\.,]\d+)?)\s*(?:tr|triệu|trieu)?/u', $normalized, $m)) {
    $priceFilterType  = 'min';
    $priceFilterValue = (float)str_replace(',', '.', $m[1]) * 1000000;
} elseif (preg_match('/\b(\d+(?:[\.,]\d+)?)\s*(?:tr|triệu|trieu)\b/u', $normalized, $m)) {
    // Nếu chỉ có số và 'tr' mà không có từ chỉ hướng thì coi là tối thiểu
    $priceFilterType  = 'min';
    $priceFilterValue = (float)str_replace(',', '.', $m[1]) * 1000000;
}

// Tách từ khóa
// Loại bỏ ký tự không phải chữ hoặc số, giữ lại tiếng Việt
$tmpQuery = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized);
$words = preg_split('/\s+/u', $tmpQuery, -1, PREG_SPLIT_NO_EMPTY);

foreach ($words as $word) {
    $w = trim($word);
    if ($w === '' || is_numeric($w)) {
        continue;
    }
    // Bỏ từ dừng
    $skip = false;
    foreach ($stopWords as $stop) {
        if ($w === $stop) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }
    $keywords[] = $w;
}

// Nếu phát hiện brand mà chưa nằm trong keywords thì thêm
if ($brand !== '' && !in_array($brand, $keywords, true)) {
    $keywords[] = $brand;
}

// Phát hiện ngữ cảnh cần ưu tiên laptop
$hasContextLaptop = false;
foreach ($contextLaptopWords as $cw) {
    if (mb_stripos($normalized, $cw, 0, 'UTF-8') !== false) {
        $hasContextLaptop = true;
        break;
    }
}

// Phát hiện có nhắc tới linh kiện cụ thể
$hasComponent = false;
foreach ($componentWords as $cw) {
    if (mb_stripos($normalized, $cw, 0, 'UTF-8') !== false) {
        $hasComponent = true;
        break;
    }
}

// Auto-thêm 'laptop' khi thuộc ngữ cảnh laptop và không nhắc tới linh kiện
if ($hasContextLaptop && !$hasComponent && !in_array('laptop', $keywords, true)) {
    $keywords[] = 'laptop';
}

/* ============================================================
   Xây dựng câu truy vấn SQL động
   ============================================================ */

$params = [];
$whereClauses = [];

// Điều kiện theo từ khóa
if (!empty($keywords)) {
    $keywordConds = [];
    foreach ($keywords as $kw) {
        // Tìm kiếm trong name, description, category, specs
        $subCond = [];
        foreach (['name','description','category','specs'] as $col) {
            $subCond[] = "LOWER($col) LIKE ?";
            $params[] = '%' . $kw . '%';
        }
        // Mỗi keyword hình thành một nhóm OR
        $keywordConds[] = '(' . implode(' OR ', $subCond) . ')';
    }
    // Kết hợp tất cả các keyword bằng OR (sản phẩm chỉ cần khớp với ít nhất 1 keyword)
    $whereClauses[] = '(' . implode(' OR ', $keywordConds) . ')';
}

// Điều kiện theo giá
if ($priceFilterType !== '' && $priceFilterValue !== null) {
    if ($priceFilterType === 'max') {
        $whereClauses[] = 'COALESCE(sale_price, price) <= ?';
    } else {
        $whereClauses[] = 'COALESCE(sale_price, price) >= ?';
    }
    $params[] = $priceFilterValue;
}

// Nếu không có keyword và cũng không có filter giá thì fallback là LIKE toàn câu hỏi
if (empty($whereClauses)) {
    // Nếu có brand thì tìm theo brand, còn không thì dùng toàn bộ câu
    if ($brand !== '') {
        $whereClauses[] = '(LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ? OR LOWER(specs) LIKE ?)';
        for ($i = 0; $i < 4; $i++) {
            $params[] = '%' . $brand . '%';
        }
    } else {
        $whereClauses[] = '(LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ? OR LOWER(specs) LIKE ?)';
        for ($i = 0; $i < 4; $i++) {
            $params[] = '%' . $normalized . '%';
        }
    }
}

// Gộp điều kiện WHERE
$whereSql = implode(' AND ', $whereClauses);

// Xác định ORDER BY: nếu có filter giá max thì sắp xếp giá tăng dần để thấy sản phẩm rẻ trước,
// nếu có filter giá min hoặc không có filter thì sắp xếp giảm dần để đưa sản phẩm đắt trước
$orderSql = '';
if ($priceFilterType === 'max') {
    $orderSql = 'ORDER BY COALESCE(sale_price, price) ASC';
} else {
    $orderSql = 'ORDER BY COALESCE(sale_price, price) DESC';
}

// SQL cuối cùng
$sql = "SELECT * FROM products WHERE $whereSql $orderSql LIMIT 20";

// Thực thi truy vấn
$info = '';
$stmt = $conn->prepare($sql);
if ($stmt) {
    // Gán tham số vào prepared statement
    if (!empty($params)) {
        // Xây dựng chuỗi kiểu (i = integer, d = double, s = string)
        // Tất cả tham số là số hoặc chuỗi
        $types = '';
        foreach ($params as $p) {
            if (is_int($p) || is_float($p)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $info .= build_product_info_line($row);
        }
        $res->free();
    }
    $stmt->close();
}

// Lưu ngữ cảnh nếu tìm được sản phẩm
if ($info !== '') {
    $_SESSION['ai_last_products'] = $info;
    $_SESSION['ai_last_question'] = $query;
}

// Nếu không có sản phẩm mới mà đã có ngữ cảnh trước -> dùng lại
if ($info === '' && !empty($_SESSION['ai_last_products'])) {
    $info = $_SESSION['ai_last_products'];
}

// Nếu vẫn chưa có gì -> fallback lấy 5 sản phẩm mới nhất
if ($info === '') {
    $res = $conn->query('SELECT * FROM products ORDER BY id DESC LIMIT 5');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $info .= build_product_info_line($row);
        }
        $res->free();
    }
}

/* ============================================================
   Tạo prompt gửi cho mô hình Gemini
   ============================================================ */

$lastQuestion = $_SESSION['ai_last_question'] ?? '';

if ($info !== '') {
    $prompt  = "Bạn là trợ lý tư vấn sản phẩm cho website TechShop.\n\n";

    if ($lastQuestion && !$newChat) {
        $prompt .= "Ở lần trước, khách đã hỏi: \"{$lastQuestion}\" và bạn đã gợi ý các sản phẩm sau:\n";
    } else {
        $prompt .= "Dưới đây là danh sách sản phẩm hiện có trong shop:\n";
    }

    $prompt .= $info . "\n";
    $prompt .= "Mỗi sản phẩm bao gồm: tên, giá, danh mục, và trường 'Thông số chính' (specs) mô tả CPU, RAM, GPU, dung lượng, màn hình, v.v.\n";
    $prompt .= "Tên sản phẩm đã được viết sẵn dưới dạng Markdown link [Tên sản phẩm](product_detail.php?id=SỐ_ID). Khi tư vấn hoặc khi khách muốn xem chi tiết, hãy sử dụng lại đúng các link này, không tự tạo link mới.\n";

    $prompt .= "Hiện tại khách hỏi: \"{$query}\".\n\n";
    $prompt .= "YÊU CẦU:\n";
    $prompt .= "- Xem đây là cuộc hội thoại liên tục: nếu khách nói \"laptop đó\", \"máy này\"… thì hiểu là đang nói về mẫu máy nổi bật bạn vừa gợi ý.\n";
    $prompt .= "- Kết hợp thông tin trong danh sách sản phẩm (tên, giá, cấu hình, mô tả) VÀ hiểu biết chung của bạn về CPU, GPU, RAM, màn hình… để đánh giá hiệu năng, phù hợp cho các nhu cầu (học tập, văn phòng, chơi game, đồ họa…).\n";
    $prompt .= "- Trả lời bằng tiếng Việt, thân thiện, dạng chat.\n";
    $prompt .= "- Mỗi sản phẩm gợi ý nên viết dạng:\n";
    $prompt .= "  ✨ **Tên máy** - GIÁ\n";
    $prompt .= "     • Điểm mạnh chính (hiệu năng / nhiệt độ / độ bền / màn hình...).\n";
    $prompt .= "- Nếu thiếu thông tin chính xác (ví dụ không rõ loại SSD) thì chỉ nói chung chung, không bịa số liệu cụ thể.\n";
} else {
    $prompt  = "Bạn là trợ lý tư vấn sản phẩm cho website TechShop.\n";
    $prompt .= "Hiện tại không lấy được dữ liệu sản phẩm từ cơ sở dữ liệu.\n";
    $prompt .= "Khách hỏi: \"{$query}\".\n";
    $prompt .= "Hãy trả lời chung chung dựa trên hiểu biết của bạn (không nêu tên mẫu cụ thể).\n";
}

/* ============================================================
   Gọi API Gemini
   ============================================================ */

// Lấy API key từ biến môi trường
$apiKey = '' ?: '';

if (!$apiKey) {
    echo json_encode(['success' => false, 'reply' => 'Chưa cấu hình khóa API Gemini.']);
    exit;
}
if (!function_exists('curl_init')) {
    echo json_encode(['success' => false, 'reply' => 'Máy chủ không hỗ trợ cURL.']);
    exit;
}

// Chọn model; dùng phiên bản mới nhất để tránh 404
$model = 'gemini-2.5-flash';

$url = 'https://generativelanguage.googleapis.com/v1beta/models/'
     . $model . ':generateContent?key=' . urlencode($apiKey);

$payload = [
    'contents' => [[
        'parts' => [[ 'text' => $prompt ]]
    ]]
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// Ghi log để debug khi cần
file_put_contents(__DIR__ . '/ai_support_log.txt',
    date('Y-m-d H:i:s') . " HTTP:$httpCode\n" .
    "CURL_ERR: $curlErr\n" .
    "RESP: $response\n\n",
    FILE_APPEND
);

// Kiểm tra response
if ($response === false) {
    echo json_encode(['success' => false, 'reply' => 'Không kết nối được tới Gemini.']);
    exit;
}

$data = json_decode($response, true);

// Nếu API trả lỗi
if ($httpCode !== 200) {
    $errMsg = $data['error']['message'] ?? 'Không rõ nguyên nhân';
    echo json_encode([
        'success' => false,
        'reply'   => "Lỗi từ Gemini (HTTP $httpCode): $errMsg"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy nội dung trả lời
if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        'success' => false,
        'reply'   => 'Dữ liệu trả về từ Gemini không đúng định dạng.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$reply = trim($data['candidates'][0]['content']['parts'][0]['text']);

// Trả về cho frontend
echo json_encode(['success' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
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

// Lưu thông tin user và session hiện tại (nếu có)
$currentUserId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
$sessionId     = isset($input['session_id']) ? (int)$input['session_id'] : 0;

/**
 * Lưu 1 tin nhắn vào bảng ai_chat_messages
 * Chỉ lưu nếu user đã đăng nhập (userId > 0)
 */
function ai_save_message(int $sessionId, int $userId, string $sender, string $message): void
{
    // Chỉ lưu lịch sử nếu user đã đăng nhập
    if ($sessionId <= 0 || $message === '' || $userId <= 0) {
        return;
    }
    global $conn;
    $stmt = $conn->prepare(
        "INSERT INTO ai_chat_messages (session_id, user_id, sender, message, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );
    if ($stmt) {
        $stmt->bind_param('iiss', $sessionId, $userId, $sender, $message);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Gửi JSON response và đồng thời lưu lịch sử chat (user + bot) nếu có session và user đã đăng nhập
 */
function ai_respond_and_log(
    bool $success,
    string $reply,
    int $sessionId,
    int $currentUserId,
    string $userMessage
): void {
    // Chỉ lưu lịch sử nếu user đã đăng nhập
    if ($currentUserId > 0) {
        // Lưu tin nhắn của user
        if ($userMessage !== '') {
            ai_save_message($sessionId, $currentUserId, 'user', $userMessage);
        }
        // Lưu tin nhắn của bot
        if ($reply !== '') {
            ai_save_message($sessionId, $currentUserId, 'bot', $reply);
        }
    }

    echo json_encode(
        [
            'success'    => $success,
            'reply'      => $reply,
            'session_id' => $sessionId,
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// Khi bắt đầu cuộc trò chuyện mới hoặc chưa có session_id -> tạo bản ghi mới trong ai_chat_sessions
if ($query !== '') {
    if ($newChat || $sessionId <= 0) {
        if ($currentUserId > 0) {
            $title = mb_substr($query, 0, 80, 'UTF-8');
            $stmtSession = $conn->prepare(
                "INSERT INTO ai_chat_sessions (user_id, title, created_at, updated_at)
                 VALUES (?, ?, NOW(), NOW())"
            );
            if ($stmtSession) {
                $stmtSession->bind_param('is', $currentUserId, $title);
                if ($stmtSession->execute()) {
                    $sessionId = $stmtSession->insert_id;
                }
                $stmtSession->close();
            }
        }
    } elseif ($sessionId > 0 && $currentUserId > 0) {
        // Cập nhật thời gian hoạt động của cuộc trò chuyện hiện tại
        $stmtUpdate = $conn->prepare(
            "UPDATE ai_chat_sessions SET updated_at = NOW() WHERE id = ? AND user_id = ?"
        );
        if ($stmtUpdate) {
            $stmtUpdate->bind_param('ii', $sessionId, $currentUserId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }
}

if ($newChat) {
    // Xóa ngữ cảnh trước đó khi bắt đầu cuộc hội thoại mới
    unset($_SESSION['ai_last_products'], $_SESSION['ai_last_question']);
}

if ($query === '') {
    ai_respond_and_log(false, '', $sessionId, $currentUserId, $query);
}

/* ============================================================
   Xử lý sớm các câu hỏi mang tính thao tác hoặc chăm sóc khách hàng
   ============================================================ */

// Chuẩn hóa về chữ thường để so khớp dễ dàng
$normalizedQuery = mb_strtolower($query, 'UTF-8');

// Xử lý số đơn giản (1, 2, 3...) để chọn sản phẩm từ danh sách trước đó
$handledNumberSelection = false;
if (preg_match('/^\s*(\d+)\s*$/u', trim($query)) && strlen(trim($query)) <= 3) {
    $num = (int)trim($query);
    if ($num >= 1 && $num <= 20 && !empty($_SESSION['ai_last_products'])) {
        // Tách danh sách sản phẩm đã gợi ý (markdown) thành mảng các link theo thứ tự
        if (preg_match_all('/product_detail\.php\?id=(\d+)/', $_SESSION['ai_last_products'], $mids)) {
            $idx = $num - 1;
            if (isset($mids[1][$idx])) {
                $productId = (int)$mids[1][$idx];
                // Lấy thông tin sản phẩm và tạo lại context
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $productId);
                    if ($stmt->execute()) {
                        $res = $stmt->get_result();
                        if ($product = $res->fetch_assoc()) {
                            // Tạo lại context chỉ với sản phẩm được chọn
                            $info = build_product_info_line($product);
                            $_SESSION['ai_last_products'] = $info;
                            $_SESSION['ai_last_question'] = $query;
                            // Cập nhật query để AI biết đang nói về sản phẩm này
                            $query = 'Giới thiệu chi tiết về ' . $product['name'];
                            $normalizedQuery = mb_strtolower($query, 'UTF-8');
                            $handledNumberSelection = true;
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}

/**
 * Lấy ID của sản phẩm đầu tiên từ ngữ cảnh AI lưu trong session.
 * @return int|null
 */
function get_first_recommended_product_id(): ?int
{
    if (empty($_SESSION['ai_last_products'])) {
        return null;
    }
    $info = $_SESSION['ai_last_products'];
    if (preg_match('/product_detail\.php\?id=(\d+)/', $info, $m)) {
        return (int)$m[1];
    }
    return null;
}

/**
 * Thêm một sản phẩm vào giỏ hàng trong phiên hiện tại. Hàm này sao chép logic
 * từ api/add_to_cart.php để cập nhật $_SESSION['cart'] và cơ sở dữ liệu khi cần.
 * @param int $productId ID sản phẩm
 * @param int $qty Số lượng cần thêm (mặc định 1)
 * @return string Thông điệp kết quả
 */
function ai_add_to_cart(int $productId, int $qty = 1): string
{
    global $conn;
    if ($productId <= 0) {
        return 'Không có sản phẩm phù hợp để thêm vào giỏ hàng.';
    }
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if (!$product) {
        return 'Sản phẩm không tồn tại.';
    }
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Kiểm tra tồn kho
    $existingQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['quantity'] : 0;
    if ($product['stock'] < $qty + $existingQty) {
        return 'Số lượng sản phẩm vượt quá tồn kho hiện có.';
    }
    // Cập nhật vào session
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$productId] = [
            'product_id' => (int)$product['id'],
            'name'       => $product['name'],
            'price'      => (float)$product['price'],
            'quantity'   => $qty
        ];
    }
    // Nếu người dùng đã đăng nhập thì cập nhật bảng cart_items
    if (isset($_SESSION['user'])) {
        $userId = (int)$_SESSION['user']['id'];
        // Kiểm tra xem sản phẩm đã có trong cart_items hay chưa
        $stmtCart = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmtCart->bind_param('ii', $userId, $productId);
        $stmtCart->execute();
        $resCart = $stmtCart->get_result();
        if ($row = $resCart->fetch_assoc()) {
            $newQty = $row['quantity'] + $qty;
            $updateCart = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $updateCart->bind_param('ii', $newQty, $row['id']);
            $updateCart->execute();
        } else {
            $insertCart = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insertCart->bind_param('iii', $userId, $productId, $qty);
            $insertCart->execute();
        }
        $stmtCart->close();
    }
    return 'Đã thêm sản phẩm "' . $product['name'] . '" vào giỏ hàng thành công.';
}

/**
 * Cố gắng xác định ID sản phẩm từ câu hỏi người dùng khi họ yêu cầu thêm vào giỏ hàng.
 * Hàm này sẽ ưu tiên tìm kiếm theo tên sản phẩm đầy đủ nằm trong câu, kế tiếp là theo
 * các từ khóa thương hiệu/phân khúc phổ biến. Nếu có nhiều kết quả, sẽ lấy kết quả
 * có tên dài nhất để giảm thiểu nhầm lẫn. Nếu không tìm được theo câu hỏi, hàm
 * trả về null để caller sử dụng fallback từ ngữ cảnh trước đó.
 *
 * @param string $normalizedQuery Câu hỏi đã được chuyển về chữ thường, bỏ dấu câu
 * @return int|null ID sản phẩm khớp, hoặc null nếu không tìm thấy
 */
function get_recommended_product_id_from_query(string $normalizedQuery): ?int
{
    global $conn;
    // Sanitize câu hỏi: chỉ giữ lại chữ cái, số và khoảng trắng để so khớp tên sản phẩm
    $sanQuery = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalizedQuery);
    // Gom các khoảng trắng liên tiếp và cắt đầu/cuối
    $sanQuery = preg_replace('/\s+/u', ' ', $sanQuery);
    $sanQuery = trim($sanQuery);
    if ($sanQuery === '') {
        return null;
    }

    // 0) Thử tìm trực tiếp một sản phẩm có tên đầy đủ nằm trong câu hỏi
    // Lặp qua toàn bộ bảng sản phẩm để tránh bỏ sót. Dataset của shop thường không quá lớn
    $bestId = null;
    $bestMatchLength = 0;
    $res = $conn->query("SELECT id, name FROM products");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $name = mb_strtolower($row['name'], 'UTF-8');
            // Loại bỏ ký tự đặc biệt trong tên sản phẩm
            $sanName = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name);
            $sanName = preg_replace('/\s+/u', ' ', $sanName);
            $sanName = trim($sanName);
            if ($sanName === '') {
                continue;
            }
            // Nếu câu hỏi chứa toàn bộ tên sản phẩm thì chọn (bỏ qua khoảng trắng để khớp "16gb" và "16 gb")
            $compactQuery = preg_replace('/\s+/u', '', $sanQuery);
            $compactName  = preg_replace('/\s+/u', '', $sanName);
            if (strpos($compactQuery, $compactName) !== false) {
                $len = mb_strlen($sanName, 'UTF-8');
                if ($len > $bestMatchLength) {
                    $bestMatchLength = $len;
                    $bestId = (int)$row['id'];
                }
            }
        }
        $res->free();
    }
    if ($bestId) {
        return $bestId;
    }

    // 1) Nếu không tìm được tên đầy đủ, thử tìm theo các từ khóa ngắn (brand/model)
    // Danh sách gợi ý brand/model. Sắp xếp theo độ dài giảm dần để ưu tiên khớp dài hơn
    $dbHints = [
        'macbook', 'thinkpad', 'vivobook', 'ideapad', 'inspiron', 'pavilion', 'nitro', 'swift',
        'gigabyte', 'lenovo', 'dell', 'asus', 'rog', 'hp', 'acer', 'msi', 'xps', 'g5', 'x1', 'mac',
        // Thêm các hãng và dòng linh kiện phổ biến
        'kingston', 'corsair', 'gskill', 'crucial', 'samsung', 'seagate', 'adata', 'pny', 'ssd', 'ram'
    ];
    foreach ($dbHints as $needle) {
        // Tìm từ khóa trong câu hỏi
        if (mb_stripos($sanQuery, $needle, 0, 'UTF-8') !== false) {
            $like = '%' . $needle . '%';
            // Truy vấn tìm sản phẩm chứa từ khóa trong tên, sắp xếp theo độ dài tên giảm dần và giá giảm dần
            $stmt = $conn->prepare(
                "SELECT id, name FROM products WHERE LOWER(name) LIKE ? ORDER BY LENGTH(name) DESC, COALESCE(sale_price, price) DESC LIMIT 1"
            );
            if ($stmt) {
                $stmt->bind_param('s', $like);
                if ($stmt->execute()) {
                    $r = $stmt->get_result();
                    if ($r && $row = $r->fetch_assoc()) {
                        $stmt->close();
                        return (int)$row['id'];
                    }
                }
                $stmt->close();
            }
        }
    }

    // 2) Nếu câu hỏi nhắc tới "sản phẩm thứ n" hoặc chỉ là số đơn giản, thử lấy theo danh sách đã tư vấn ở session
    $selectedIndex = null;
    
    // Kiểm tra "thứ n" hoặc "thu n"
    if (preg_match('/th(?:ứ|u)\s*(\d+)/u', $sanQuery, $mm)) {
        $selectedIndex = max(1, (int)$mm[1]) - 1;
    }
    // Kiểm tra số đơn giản (1-10) - chỉ khi câu hỏi rất ngắn (có thể là chọn số)
    elseif (preg_match('/^\s*(\d+)\s*$/u', trim($sanQuery)) && strlen(trim($sanQuery)) <= 3) {
        $num = (int)trim($sanQuery);
        if ($num >= 1 && $num <= 20) {
            $selectedIndex = $num - 1;
        }
    }
    
    if ($selectedIndex !== null && !empty($_SESSION['ai_last_products'])) {
        // Tách danh sách sản phẩm đã gợi ý (markdown) thành mảng các link theo thứ tự
        if (preg_match_all('/product_detail\.php\?id=(\d+)/', $_SESSION['ai_last_products'], $mids)) {
            if (isset($mids[1][$selectedIndex])) {
                return (int)$mids[1][$selectedIndex];
            }
        }
    }
    return null;
}

// Các câu hỏi chăm sóc khách hàng cơ bản và trả lời cố định
// Các câu hỏi chăm sóc khách hàng cơ bản và trả lời cố định
// Mở rộng thêm nhiều biến thể để khách dễ hỏi theo cách khác nhau
$supportResponses = [
    // Chính sách bảo hành
    'bảo hành'       => 'Tất cả sản phẩm tại TechShop đều có chế độ bảo hành chính hãng. Thời gian bảo hành tùy thuộc từng danh mục, thường từ 12–24 tháng. Bạn vui lòng kiểm tra thông tin bảo hành cụ thể trên trang chi tiết sản phẩm hoặc liên hệ hotline để được hỗ trợ.',
    'bảo trì'        => 'Tất cả sản phẩm tại TechShop đều có chế độ bảo hành chính hãng. Thời gian bảo hành tùy thuộc từng danh mục, thường từ 12–24 tháng. Bạn vui lòng kiểm tra thông tin bảo hành cụ thể trên trang chi tiết sản phẩm hoặc liên hệ hotline để được hỗ trợ.',
    // Chính sách đổi trả và hủy đơn
    'đổi trả'        => 'Chính sách đổi trả: trong vòng 7 ngày kể từ khi nhận hàng, sản phẩm chưa qua sử dụng và còn nguyên tem mác sẽ được hỗ trợ đổi sang mẫu khác hoặc hoàn tiền. Vui lòng giữ hóa đơn và liên hệ hotline trước khi gửi sản phẩm.',
    'hủy đơn'        => 'Để hủy đơn hàng, bạn vui lòng liên hệ hotline 1900‑1234 hoặc gửi email tới support@techshop.vn kèm mã đơn hàng. Nhân viên sẽ hỗ trợ kiểm tra và hủy đơn trong thời gian sớm nhất.',
    // Giao hàng và vận chuyển
    'giao hàng'      => 'TechShop hỗ trợ giao hàng toàn quốc. Thời gian giao hàng dự kiến 1–3 ngày tại nội thành và 3–7 ngày với tỉnh thành khác. Đơn hàng trên 1 triệu đồng được miễn phí vận chuyển.',
    'vận chuyển'     => 'TechShop hỗ trợ giao hàng toàn quốc. Thời gian giao hàng dự kiến 1–3 ngày tại nội thành và 3–7 ngày với tỉnh thành khác. Đơn hàng trên 1 triệu đồng được miễn phí vận chuyển.',
    'phí ship'       => 'Đơn hàng trên 1 triệu đồng được miễn phí vận chuyển. Với đơn hàng dưới mức này, phí ship sẽ được hiển thị tại bước thanh toán và phụ thuộc vào địa chỉ giao hàng.',
    // Thanh toán và trả góp
    'thanh toán'     => 'Bạn có thể thanh toán bằng tiền mặt khi nhận hàng (COD), chuyển khoản ngân hàng hoặc qua ví điện tử. Hệ thống sẽ hiển thị tùy chọn khi bạn tiến hành đặt hàng.',
    'trả góp'        => 'TechShop hỗ trợ mua hàng trả góp qua thẻ tín dụng của nhiều ngân hàng. Bạn vui lòng chọn hình thức trả góp tại bước thanh toán hoặc liên hệ hotline để biết thêm chi tiết.',
    // Liên hệ và hỗ trợ
    'liên hệ'        => 'Bạn có thể liên hệ chúng tôi qua số điện thoại 1900‑1234, email support@techshop.vn hoặc fanpage TechShop để được hỗ trợ nhanh nhất.',
    'địa chỉ'        => 'TechShop có trụ sở tại 123 Đường Cách Mạng Tháng Tám, Quận 3, TP. HCM và chi nhánh tại 456 Phố Huế, Hà Nội. Bạn có thể ghé thăm để trải nghiệm sản phẩm trực tiếp.',
    'giờ mở cửa'      => 'Cửa hàng TechShop mở cửa từ 9h sáng đến 9h tối tất cả các ngày trong tuần.',
];

// Kiểm tra nếu người dùng yêu cầu thêm sản phẩm vào giỏ
if (preg_match('/(?:thêm|them|mua)\s+(?:.*)?(?:vào giỏ|vao gio|giỏ hàng|gio hang|mua ngay)/u', $normalizedQuery)) {

    // Cố gắng trích xuất cụm tên sản phẩm mà người dùng muốn thêm
    $explicitName = '';
    if (preg_match('/(?:thêm|them|mua)\s+(.*?)\s*(?:vào giỏ|vao gio|giỏ hàng|gio hang|mua ngay)/u', $normalizedQuery, $mmName)) {
        $explicitName = trim($mmName[1]);
    }

    // Các cách gọi chung chung cho sản phẩm vừa được gợi ý
    $genericRefs = [
        'sản phẩm này', 'san pham nay', 'sản phẩm đó', 'san pham do', 'sản phẩm kia', 'san pham kia',
        'laptop này', 'laptop đó', 'laptop kia',
        'máy này', 'may nay', 'máy đó', 'may do', 'máy kia', 'may kia',
        'sp này', 'sp đó', 'sp kia'
    ];

    $isGenericRef = false;
    if ($explicitName !== '') {
        foreach ($genericRefs as $gr) {
            if ($explicitName === $gr) {
                $isGenericRef = true;
                break;
            }
        }
    }

    // 1) Ưu tiên xác định đúng sản phẩm theo nội dung câu hỏi
    $pid = get_recommended_product_id_from_query($normalizedQuery);

    // 2) Nếu người dùng gọi đích danh một sản phẩm nhưng không tìm thấy trong DB
    //    thì KHÔNG fallback sang sản phẩm đầu danh sách để tránh thêm sai.
    if (!$pid && $explicitName !== '' && !$isGenericRef) {
        $message = 'Không tìm thấy đúng sản phẩm bạn muốn thêm vào giỏ. Bạn có thể kiểm tra lại tên sản phẩm hoặc để mình gợi ý mẫu phù hợp nhé.';
        ai_respond_and_log(true, $message, $sessionId, $currentUserId, $query);
    }

    // 3) Nếu không tìm được sản phẩm cụ thể và người dùng chỉ nói chung chung
    //    thì fallback lấy sản phẩm đầu trong ngữ cảnh AI
    if (!$pid) {
        $pid = get_first_recommended_product_id();
    }

    if ($pid !== null) {
        $message = ai_add_to_cart((int)$pid, 1);
    } else {
        $message = 'Hiện không có sản phẩm được đề xuất để thêm vào giỏ hàng.';
    }

    ai_respond_and_log(true, $message, $sessionId, $currentUserId, $query);
}

// Kiểm tra các câu hỏi chăm sóc khách hàng và trả về câu trả lời cố định
foreach ($supportResponses as $keyword => $responseText) {
    if (mb_stripos($normalizedQuery, $keyword, 0, 'UTF-8') !== false) {
        ai_respond_and_log(true, $responseText, $sessionId, $currentUserId, $query);
    }
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
    , 'dưới', 'duoi', 'trên', 'tren'
    , 'sản', 'san', 'phẩm', 'pham'
    , 'tư', 'tu', 'vấn', 'van', 'giá', 'gia', 'giá cả', 'gia ca', 'triệu', 'trieu', 'tr'
];

// Danh sách hãng cần nhận diện
$brandNames = ['msi', 'dell', 'acer', 'asus', 'lenovo', 'hp', 'gigabyte', 'apple', 'macbook', 'kingston', 'gskill', 'corsair', 'crucial', 'samsung', 'seagate', 'adata', 'pny'];

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

// Phát hiện có nhắc tới linh kiện cụ thể - kiểm tra kỹ hơn
$hasComponent = false;
$detectedComponentEarly = '';
foreach ($componentWords as $cw) {
    // Kiểm tra trong câu hỏi gốc
    if (mb_stripos($normalized, $cw, 0, 'UTF-8') !== false) {
        $hasComponent = true;
        $detectedComponentEarly = $cw;
        // Đảm bảo component được thêm vào keywords
        if (!in_array($cw, $keywords, true)) {
            $keywords[] = $cw;
        }
        break;
    }
}

// Auto-thêm 'laptop' CHỈ KHI không có component và thuộc ngữ cảnh laptop
if ($hasContextLaptop && !$hasComponent && !in_array('laptop', $keywords, true)) {
    $keywords[] = 'laptop';
}

// Auto-thêm 'laptop' theo ngân sách cao nếu người dùng chỉ hỏi mức giá chung
// NHƯNG CHỈ KHI KHÔNG CÓ COMPONENT
$hasPriceIntent = ($priceFilterType !== '' && $priceFilterValue !== null);
$mentionsAccessories = (mb_stripos($normalized, 'phụ kiện', 0, 'UTF-8') !== false) ||
                      (mb_stripos($normalized, 'phu kien', 0, 'UTF-8') !== false) ||
                      (mb_stripos($normalized, 'accessory', 0, 'UTF-8') !== false);
if ($hasPriceIntent && $priceFilterValue >= 10000000 && !$hasComponent && !$mentionsAccessories && !in_array('laptop', $keywords, true)) {
    $keywords[] = 'laptop';
}

// Nếu có component, LOẠI BỎ 'laptop' khỏi keywords để tránh tìm nhầm
if ($hasComponent && in_array('laptop', $keywords, true)) {
    $keywords = array_values(array_filter($keywords, function($kw) {
        return $kw !== 'laptop';
    }));
}

/* ============================================================
   Xây dựng câu truy vấn SQL động
   ============================================================ */

$params = [];
$whereClauses = [];

// Phát hiện component trong keywords hoặc trong câu hỏi gốc
// Ưu tiên dùng component đã phát hiện sớm
$detectedComponent = $detectedComponentEarly;
// Nếu chưa có, tìm trong keywords
if ($detectedComponent === '') {
    foreach ($componentWords as $comp) {
        if (in_array($comp, $keywords, true)) {
            $detectedComponent = $comp;
            break;
        }
    }
}
// Nếu vẫn chưa có, tìm trong câu hỏi gốc
if ($detectedComponent === '') {
    foreach ($componentWords as $comp) {
        if (mb_stripos($normalized, $comp, 0, 'UTF-8') !== false) {
            $detectedComponent = $comp;
            // Thêm vào keywords nếu chưa có
            if (!in_array($comp, $keywords, true)) {
                $keywords[] = $comp;
            }
            break;
        }
    }
}

// Nếu có cả brand và component, ưu tiên tìm sản phẩm có CẢ HAI
if ($brand !== '' && $detectedComponent !== '') {
    // Điều kiện bắt buộc: phải có cả brand VÀ component
    $brandComponentCond = [];
    
    // Brand phải có trong name hoặc description hoặc specs
    $brandCond = [];
    foreach (['name','description','specs'] as $col) {
        $brandCond[] = "LOWER($col) LIKE ?";
        $params[] = '%' . $brand . '%';
    }
    $brandComponentCond[] = '(' . implode(' OR ', $brandCond) . ')';
    
    // Component phải có trong name hoặc description hoặc category hoặc specs
    $compCond = [];
    foreach (['name','description','category','specs'] as $col) {
        $compCond[] = "LOWER($col) LIKE ?";
        $params[] = '%' . $detectedComponent . '%';
    }
    $brandComponentCond[] = '(' . implode(' OR ', $compCond) . ')';
    
    // Kết hợp bằng AND để đảm bảo có cả hai
    $whereClauses[] = '(' . implode(' AND ', $brandComponentCond) . ')';
    
    // Loại bỏ brand và component đã dùng khỏi keywords để tránh lặp lại
    $keywords = array_filter($keywords, function($kw) use ($brand, $detectedComponent) {
        return $kw !== $brand && $kw !== $detectedComponent;
    });
    $keywords = array_values($keywords); // Re-index array
}

// Nếu có component nhưng chưa có trong whereClauses (chưa có brand+component), thêm điều kiện component
if ($detectedComponent !== '' && empty($whereClauses)) {
    $compWhere = [];
    foreach (['name','description','category','specs'] as $col) {
        $compWhere[] = "LOWER($col) LIKE ?";
        $params[] = '%' . $detectedComponent . '%';
    }
    $whereClauses[] = '(' . implode(' OR ', $compWhere) . ')';
    
    // Loại bỏ component và laptop khỏi keywords để tránh lặp
    $keywords = array_filter($keywords, function($kw) use ($detectedComponent) {
        return $kw !== $detectedComponent && $kw !== 'laptop';
    });
    $keywords = array_values($keywords);
}

// Điều kiện theo từ khóa còn lại
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
    // Nếu đã có điều kiện brand+component hoặc component thì thêm keywords bằng AND
    // Nếu chưa có thì dùng OR như cũ
    if (!empty($whereClauses)) {
        // Đã có điều kiện, thêm keywords bằng AND để tăng độ chính xác
        $whereClauses[] = '(' . implode(' OR ', $keywordConds) . ')';
    } else {
        // Chưa có điều kiện nào, dùng OR như cũ
        $whereClauses[] = '(' . implode(' OR ', $keywordConds) . ')';
    }
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

// Xác định ORDER BY: ưu tiên sản phẩm khớp nhiều keywords hơn
$orderSql = '';
if ($priceFilterType === 'max') {
    $orderSql = 'ORDER BY COALESCE(sale_price, price) ASC';
} else {
    // Nếu có component, ưu tiên sản phẩm có component trong name và category
    if ($detectedComponent !== '') {
        $escapedComponent = $conn->real_escape_string($detectedComponent);
        if ($brand !== '') {
            // Có cả brand và component
            $escapedBrand = $conn->real_escape_string($brand);
            $orderSql = "ORDER BY 
                CASE 
                    WHEN LOWER(name) LIKE '%{$escapedBrand}%' AND LOWER(name) LIKE '%{$escapedComponent}%' THEN 1
                    WHEN LOWER(name) LIKE '%{$escapedComponent}%' AND LOWER(category) LIKE '%{$escapedComponent}%' THEN 2
                    WHEN LOWER(name) LIKE '%{$escapedBrand}%' OR LOWER(name) LIKE '%{$escapedComponent}%' THEN 3
                    ELSE 4
                END,
                COALESCE(sale_price, price) DESC";
        } else {
            // Chỉ có component - ưu tiên component trong name và category, loại bỏ laptop
            $orderSql = "ORDER BY 
                CASE 
                    WHEN LOWER(name) LIKE '%{$escapedComponent}%' AND LOWER(category) LIKE '%{$escapedComponent}%' THEN 1
                    WHEN LOWER(name) LIKE '%{$escapedComponent}%' THEN 2
                    WHEN LOWER(name) NOT LIKE '%laptop%' AND LOWER(category) LIKE '%{$escapedComponent}%' THEN 3
                    ELSE 4
                END,
                COALESCE(sale_price, price) DESC";
        }
    } else {
        $orderSql = 'ORDER BY COALESCE(sale_price, price) DESC';
    }
}

// SQL cuối cùng
$sql = "SELECT * FROM products WHERE $whereSql $orderSql LIMIT 20";

// Thực thi truy vấn
$info = '';
$foundProducts = false;
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
            $foundProducts = true;
        }
        $res->free();
    }
    $stmt->close();
}

// Nếu có brand+component nhưng không tìm được sản phẩm, fallback về OR
if (!$foundProducts && $brand !== '' && $detectedComponent !== '') {
    // Thử lại với OR thay vì AND
    $fallbackParams = [];
    $fallbackWhere = [];
    
    // Brand hoặc component trong bất kỳ trường nào
    $fallbackCond = [];
    foreach (['name','description','category','specs'] as $col) {
        $fallbackCond[] = "(LOWER($col) LIKE ? OR LOWER($col) LIKE ?)";
        $fallbackParams[] = '%' . $brand . '%';
        $fallbackParams[] = '%' . $detectedComponent . '%';
    }
    $fallbackWhere[] = '(' . implode(' OR ', $fallbackCond) . ')';
    
    // Thêm điều kiện giá nếu có
    if ($priceFilterType !== '' && $priceFilterValue !== null) {
        if ($priceFilterType === 'max') {
            $fallbackWhere[] = 'COALESCE(sale_price, price) <= ?';
        } else {
            $fallbackWhere[] = 'COALESCE(sale_price, price) >= ?';
        }
        $fallbackParams[] = $priceFilterValue;
    }
    
    $fallbackSql = "SELECT * FROM products WHERE " . implode(' AND ', $fallbackWhere) . " " . $orderSql . " LIMIT 20";
    
    $stmtFallback = $conn->prepare($fallbackSql);
    if ($stmtFallback) {
        if (!empty($fallbackParams)) {
            $types = '';
            foreach ($fallbackParams as $p) {
                if (is_int($p) || is_float($p)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmtFallback->bind_param($types, ...$fallbackParams);
        }
        if ($stmtFallback->execute()) {
            $res = $stmtFallback->get_result();
            $info = '';
            while ($row = $res->fetch_assoc()) {
                $info .= build_product_info_line($row);
                $foundProducts = true;
            }
            $res->free();
        }
        $stmtFallback->close();
    }
}

// Nếu vẫn không tìm được và có brand, thử tìm chỉ theo brand
if (!$foundProducts && $brand !== '') {
    $brandOnlyParams = [];
    $brandOnlyWhere = ['(LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ? OR LOWER(specs) LIKE ?)'];
    for ($i = 0; $i < 4; $i++) {
        $brandOnlyParams[] = '%' . $brand . '%';
    }
    
    if ($priceFilterType !== '' && $priceFilterValue !== null) {
        if ($priceFilterType === 'max') {
            $brandOnlyWhere[] = 'COALESCE(sale_price, price) <= ?';
        } else {
            $brandOnlyWhere[] = 'COALESCE(sale_price, price) >= ?';
        }
        $brandOnlyParams[] = $priceFilterValue;
    }
    
    $brandOnlySql = "SELECT * FROM products WHERE " . implode(' AND ', $brandOnlyWhere) . " " . $orderSql . " LIMIT 20";
    
    $stmtBrand = $conn->prepare($brandOnlySql);
    if ($stmtBrand) {
        $types = '';
        foreach ($brandOnlyParams as $p) {
            if (is_int($p) || is_float($p)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmtBrand->bind_param($types, ...$brandOnlyParams);
        if ($stmtBrand->execute()) {
            $res = $stmtBrand->get_result();
            $info = '';
            while ($row = $res->fetch_assoc()) {
                $info .= build_product_info_line($row);
                $foundProducts = true;
            }
            $res->free();
        }
        $stmtBrand->close();
    }
}

// Lưu ngữ cảnh nếu tìm được sản phẩm (trừ khi đã xử lý số selection)
if ($info !== '' && !$handledNumberSelection) {
    $_SESSION['ai_last_products'] = $info;
    $_SESSION['ai_last_question'] = $query;
}

// Nếu không có sản phẩm mới mà đã có ngữ cảnh trước -> dùng lại (trừ khi đã xử lý số)
if ($info === '' && !empty($_SESSION['ai_last_products']) && !$handledNumberSelection) {
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
    // Bổ sung hướng dẫn để mô hình tham khảo kiến thức bên ngoài cơ sở dữ liệu của shop khi cần
    $prompt .= "- Nếu khách hỏi về thông tin không có trong mô tả (chẳng hạn thời lượng pin, trọng lượng, bảo hành...), hãy trả lời dựa trên hiểu biết chung và kiến thức trực tuyến của bạn.\n";
} else {
    $prompt  = "Bạn là trợ lý tư vấn sản phẩm cho website TechShop.\n";
    $prompt .= "Hiện tại không lấy được dữ liệu sản phẩm từ cơ sở dữ liệu.\n";
    $prompt .= "Khách hỏi: \"{$query}\".\n";
    $prompt .= "Hãy trả lời chung chung dựa trên hiểu biết của bạn (không nêu tên mẫu cụ thể).\n";
}

/* ============================================================
   Gọi API Gemini
   ============================================================ */

$apiKey = '' ?: '';

if (!$apiKey) {
    ai_respond_and_log(false, 'Chưa cấu hình khóa API Gemini.', $sessionId, $currentUserId, $query);
}
if (!function_exists('curl_init')) {
    ai_respond_and_log(false, 'Máy chủ không hỗ trợ cURL.', $sessionId, $currentUserId, $query);
}

// Chọn model
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

// Ghi log
file_put_contents(__DIR__ . '/ai_support_log.txt',
    date('Y-m-d H:i:s') . " HTTP:$httpCode\n" .
    "CURL_ERR: $curlErr\n" .
    "RESP: $response\n\n",
    FILE_APPEND
);

// Kiểm tra response
if ($response === false) {
    ai_respond_and_log(false, 'Không kết nối được tới Gemini.', $sessionId, $currentUserId, $query);
}

$data = json_decode($response, true);

// Nếu API trả lỗi
if ($httpCode !== 200) {
    $errMsg = $data['error']['message'] ?? 'Không rõ nguyên nhân';
    $msg = "Lỗi từ Gemini (HTTP $httpCode): $errMsg";
    ai_respond_and_log(false, $msg, $sessionId, $currentUserId, $query);
}

// Lấy nội dung trả lời
if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    ai_respond_and_log(false, 'Dữ liệu trả về từ Gemini không đúng định dạng.', $sessionId, $currentUserId, $query);
}

$reply = trim($data['candidates'][0]['content']['parts'][0]['text']);

// Trả về cho frontend và đồng thời lưu lịch sử chat
ai_respond_and_log(true, $reply, $sessionId, $currentUserId, $query);

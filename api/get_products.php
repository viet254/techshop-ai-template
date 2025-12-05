<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';

// Tìm kiếm sản phẩm theo tên hoặc danh mục nếu có tham số q
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
// Lọc sản phẩm theo danh mục nếu có tham số cat
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

$products = [];

// Kiểm tra xem cột sale_price có tồn tại trong bảng products không
$hasSale = false;
$colRes = $conn->query("SHOW COLUMNS FROM products LIKE 'sale_price'");
if ($colRes && $colRes->num_rows > 0) {
    $hasSale = true;
}

// Xác định các trường cần chọn tùy theo việc có cột sale_price hay không
$baseFields = 'id, name, price, image, description, specs, category';
$selectFields = $hasSale ? 'id, name, price, sale_price, image, description, specs, category' : $baseFields;

if ($q !== '') {
    // Tìm kiếm theo tên hoặc danh mục
    $like = '%' . $q . '%';
    $sql = "SELECT $selectFields FROM products WHERE name LIKE ? OR category LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} elseif ($cat !== '') {
    // Lọc theo danh mục chính xác (không phân biệt chữ hoa thường)
    $sql = "SELECT $selectFields FROM products WHERE LOWER(category) LIKE LOWER(?)";
    $stmt = $conn->prepare($sql);
    // Ghép ký tự % vào trước và sau để tìm kiếm partial match
    $catLike = '%' . $cat . '%';
    $stmt->bind_param('s', $catLike);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    // Không có tham số, trả về tất cả
    $sql = "SELECT $selectFields FROM products";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

echo json_encode($products);
?>

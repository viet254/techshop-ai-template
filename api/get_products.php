<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
// Tìm kiếm sản phẩm theo tên hoặc danh mục nếu có tham số q
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
// Lọc sản phẩm theo danh mục nếu có tham số cat
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$products = [];
if ($q !== '') {
    // Tìm kiếm theo tên hoặc danh mục
    $like = '%' . $q . '%';
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE name LIKE ? OR category LIKE ?");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} elseif ($cat !== '') {
    // Lọc theo danh mục chính xác (không phân biệt chữ hoa thường)
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE LOWER(category) LIKE LOWER(?)");
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
    $result = $conn->query("SELECT id, name, price, image FROM products");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}
echo json_encode($products);
?>
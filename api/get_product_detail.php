<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Kiểm tra cột sale_price có tồn tại không
$hasSale = false;
$colRes = $conn->query("SHOW COLUMNS FROM products LIKE 'sale_price'");
if ($colRes && $colRes->num_rows > 0) {
    $hasSale = true;
}
// Xác định trường cần truy vấn
$fields = $hasSale ? 'id, name, price, sale_price, image, description, specs, category, stock' : 'id, name, price, image, description, specs, category, stock';
// Lấy sản phẩm
$sql = "SELECT $fields FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
// Lấy kết quả sản phẩm
$product = $result->fetch_assoc();
// Nếu tìm thấy sản phẩm và đây là laptop Dell Inspiron 15 5510 (id 29) thì ghi đè thông số kỹ thuật
if ($product && (int)$product['id'] === 29) {
    // Thông số kỹ thuật mới theo yêu cầu
    $product['specs'] = 'CPU: Core i5 13420H; RAM: 16GB; SSD: 512GB NVMe; Card: Intel Graphics; Màn hình: 15 inch FHD';
}
echo json_encode($product ?: new stdClass());
?>
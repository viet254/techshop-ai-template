<?php
// Lấy danh sách sản phẩm lưu của người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$items = [];
if (isset($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT si.product_id, si.quantity, p.name, p.price FROM saved_items si JOIN products p ON si.product_id = p.id WHERE si.user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $items[] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'quantity' => (int)$row['quantity']
        ];
    }
} else {
    $items = array_values($_SESSION['saved'] ?? []);
}
echo json_encode($items);
?>
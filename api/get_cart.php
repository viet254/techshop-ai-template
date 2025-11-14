<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
// Chuẩn bị mảng giỏ hàng và saved
$cart = [];
$saved = [];
if (isset($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    // Lấy cart items từ DB
    $stmtCart = $conn->prepare("SELECT ci.product_id, ci.quantity, p.name, p.price FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?");
    $stmtCart->bind_param('i', $userId);
    $stmtCart->execute();
    $resultCart = $stmtCart->get_result();
    while ($row = $resultCart->fetch_assoc()) {
        $cart[$row['product_id']] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'quantity' => (int)$row['quantity']
        ];
    }
    // Cập nhật session cart để đồng bộ
    $_SESSION['cart'] = $cart;
    // Lấy saved_items từ DB
    $stmtSaved = $conn->prepare("SELECT si.product_id, si.quantity, p.name, p.price FROM saved_items si JOIN products p ON si.product_id = p.id WHERE si.user_id = ?");
    $stmtSaved->bind_param('i', $userId);
    $stmtSaved->execute();
    $resultSaved = $stmtSaved->get_result();
    while ($row = $resultSaved->fetch_assoc()) {
        $saved[$row['product_id']] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'quantity' => (int)$row['quantity']
        ];
    }
} else {
    // Nếu chưa đăng nhập, sử dụng session 'cart' và 'saved'
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $saved = isset($_SESSION['saved']) ? $_SESSION['saved'] : [];
}
echo json_encode(['cart' => $cart, 'saved' => $saved]);
?>
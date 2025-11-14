<?php
// Xóa sản phẩm khỏi giỏ hoặc saved
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$saved = isset($data['saved']) ? (bool)$data['saved'] : false;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}
if ($saved) {
    // Xóa trong saved
    if (isset($_SESSION['user'])) {
        $userId = (int)$_SESSION['user']['id'];
        $stmt = $conn->prepare("DELETE FROM saved_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $userId, $id);
        $stmt->execute();
    } else {
        if (isset($_SESSION['saved'][$id])) unset($_SESSION['saved'][$id]);
    }
} else {
    // Xóa trong cart
    if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
    // Nếu đăng nhập, xóa khỏi bảng cart_items
    if (isset($_SESSION['user'])) {
        $userId = (int)$_SESSION['user']['id'];
        $delCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $delCart->bind_param('ii', $userId, $id);
        $delCart->execute();
    }
}
echo json_encode(['success' => true]);
?>
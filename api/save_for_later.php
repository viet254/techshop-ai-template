<?php
// Di chuyển sản phẩm từ giỏ sang danh sách lưu mua sau
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
if (!isset($_SESSION['cart'][$id])) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
    exit;
}
$item = $_SESSION['cart'][$id];
unset($_SESSION['cart'][$id]);
// Nếu đăng nhập, xóa khỏi bảng cart_items và lưu vào bảng saved_items
if (isset($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    // Xóa khỏi cart_items
    $delCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
    $delCart->bind_param('ii', $userId, $id);
    $delCart->execute();
    // Kiểm tra nếu đã tồn tại thì tăng số lượng
    $stmt = $conn->prepare("SELECT id, quantity FROM saved_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $userId, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $newQty = $row['quantity'] + $item['quantity'];
        $update = $conn->prepare("UPDATE saved_items SET quantity = ? WHERE id = ?");
        $update->bind_param('ii', $newQty, $row['id']);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO saved_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param('iii', $userId, $id, $item['quantity']);
        $insert->execute();
    }
} else {
    // Lưu vào session nếu chưa đăng nhập
    if (!isset($_SESSION['saved'])) $_SESSION['saved'] = [];
    if (isset($_SESSION['saved'][$id])) {
        $_SESSION['saved'][$id]['quantity'] += $item['quantity'];
    } else {
        $_SESSION['saved'][$id] = $item;
    }
}
echo json_encode(['success' => true]);
?>
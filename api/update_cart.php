<?php
// Cập nhật số lượng sản phẩm trong giỏ
header('Content-Type: application/json');
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;
if (!isset($_SESSION['cart'][$id])) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
    exit;
}
if ($qty <= 0) {
    unset($_SESSION['cart'][$id]);
    // Nếu đăng nhập, xóa khỏi bảng cart_items
    if (isset($_SESSION['user'])) {
        require_once __DIR__ . '/../database/connect.php';
        $userId = (int)$_SESSION['user']['id'];
        $delCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $delCart->bind_param('ii', $userId, $id);
        $delCart->execute();
    }
} else {
    // Kiểm tra tồn kho
    require_once __DIR__ . '/../database/connect.php';
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row && $qty > $row['stock']) {
        echo json_encode(['success' => false, 'message' => 'Vượt quá số lượng tồn kho']);
        exit;
    }
    $_SESSION['cart'][$id]['quantity'] = $qty;
    // Nếu đăng nhập, cập nhật bảng cart_items
    if (isset($_SESSION['user'])) {
        $userId = (int)$_SESSION['user']['id'];
        $updateCart = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $updateCart->bind_param('iii', $qty, $userId, $id);
        $updateCart->execute();
    }
}
echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
?>
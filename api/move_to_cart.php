<?php
// Di chuyển sản phẩm từ lưu mua sau về giỏ
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}
$quantity = 0;
// Nếu đăng nhập
if (isset($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    // Lấy thông tin saved item
    $stmt = $conn->prepare("SELECT id, quantity FROM saved_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $userId, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $quantity = $row['quantity'];
        // Xóa khỏi bảng saved_items
        $del = $conn->prepare("DELETE FROM saved_items WHERE id = ?");
        $del->bind_param('i', $row['id']);
        $del->execute();
    }
} else {
    // Xử lý session saved
    if (isset($_SESSION['saved'][$id])) {
        $quantity = $_SESSION['saved'][$id]['quantity'];
        unset($_SESSION['saved'][$id]);
    }
}
// Thêm vào giỏ
if ($quantity > 0) {
    // Lấy thông tin sản phẩm
    $stmtP = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmtP->bind_param('i', $id);
    $stmtP->execute();
    $p = $stmtP->get_result()->fetch_assoc();
    if ($p) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$id] = [
                'product_id' => $p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'quantity' => $quantity
            ];
        }
            // Nếu đăng nhập, lưu vào bảng cart_items
            if (isset($_SESSION['user'])) {
                $userId = (int)$_SESSION['user']['id'];
                // Kiểm tra đã tồn tại
                $stmtCart = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                $stmtCart->bind_param('ii', $userId, $id);
                $stmtCart->execute();
                $resCart = $stmtCart->get_result();
                if ($rowCart = $resCart->fetch_assoc()) {
                    $newQty = $rowCart['quantity'] + $quantity;
                    $updateCart = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                    $updateCart->bind_param('ii', $newQty, $rowCart['id']);
                    $updateCart->execute();
                } else {
                    $insertCart = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $insertCart->bind_param('iii', $userId, $id, $quantity);
                    $insertCart->execute();
                }
            }
    }
}
echo json_encode(['success' => true]);
?>
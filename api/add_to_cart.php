<?php
// Thêm sản phẩm vào giỏ hàng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}
// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}
// Cập nhật session giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
// Kiểm tra tồn kho: nếu số lượng thêm vượt quá tồn kho thì thông báo lỗi
$existingQty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id]['quantity'] : 0;
if ($product['stock'] < $qty + $existingQty) {
    echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm vượt quá tồn kho']);
    exit;
}
// If item exists, increment quantity
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity'] += $qty;
} else {
    $_SESSION['cart'][$id] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => (float)$product['price'],
        'quantity' => $qty
    ];
}
// Nếu đã đăng nhập, lưu giỏ hàng vào cơ sở dữ liệu để duy trì qua phiên
if (isset($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    // Kiểm tra xem sản phẩm đã tồn tại trong bảng cart_items chưa
    $stmtCart = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmtCart->bind_param('ii', $userId, $id);
    $stmtCart->execute();
    $resCart = $stmtCart->get_result();
    if ($row = $resCart->fetch_assoc()) {
        $newQty = $row['quantity'] + $qty;
        $updateCart = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $updateCart->bind_param('ii', $newQty, $row['id']);
        $updateCart->execute();
    } else {
        $insertCart = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertCart->bind_param('iii', $userId, $id, $qty);
        $insertCart->execute();
    }
}
echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
?>
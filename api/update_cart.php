<?php
// Cập nhật số lượng sản phẩm trong giỏ
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../includes/cart_service.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;
cart_ensure_session_bucket('cart');
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
    exit;
}
$userId = cart_get_user_id();
$hasItem = $userId
    ? cart_get_db_item_quantity($conn, $userId, $id) > 0
    : isset($_SESSION['cart'][$id]);
if (!$hasItem) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
    exit;
}
if ($qty <= 0) {
    cart_remove_session_item($id);
    if ($userId) {
        cart_remove_db_item($conn, $userId, $id);
    }
    $items = cart_fetch_items($conn);
    echo json_encode([
        'success' => true,
        'cart' => cart_items_to_map($items),
        'items' => $items,
        'summary' => cart_calculate_summary($items)
    ]);
    exit;
}
$product = cart_fetch_product($conn, $id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa']);
    exit;
}
if ($qty > $product['stock']) {
    $qty = $product['stock'];
    if ($qty <= 0) {
        cart_remove_session_item($id);
        if ($userId) {
            cart_remove_db_item($conn, $userId, $id);
        }
        echo json_encode([
            'success' => false,
            'removed' => true,
            'message' => 'Sản phẩm đã hết hàng và bị xóa khỏi giỏ.'
        ]);
        exit;
    }
    $message = 'Vượt quá số lượng tồn kho, đã chuyển về số lượng tối đa (' . $qty . ').';
} else {
    $message = 'Đã cập nhật số lượng.';
}
cart_set_session_item($product, $qty);
if ($userId) {
    cart_upsert_db_item($conn, $userId, $id, $qty);
}
$items = cart_fetch_items($conn);
echo json_encode([
    'success' => true,
    'message' => $message,
    'cart' => cart_items_to_map($items),
    'items' => $items,
    'summary' => cart_calculate_summary($items)
]);
?>
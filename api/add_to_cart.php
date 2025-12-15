<?php
// Thêm sản phẩm vào giỏ hàng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../includes/cart_service.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$qty = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}
$product = cart_fetch_product($conn, $id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}
cart_ensure_session_bucket('cart');
$userId = cart_get_user_id();
$existingQty = $userId
    ? cart_get_db_item_quantity($conn, $userId, $id)
    : cart_get_session_item_quantity($id);
$newQty = $existingQty + $qty;
if ($product['stock'] < $newQty) {
    echo json_encode([
        'success' => false,
        'message' => 'Số lượng sản phẩm vượt quá tồn kho (' . $product['stock'] . ' sản phẩm khả dụng)'
    ]);
    exit;
}
if ($userId) {
    cart_upsert_db_item($conn, $userId, $id, $newQty);
}
cart_set_session_item($product, $newQty);
$items = cart_fetch_items($conn);
$summary = cart_calculate_summary($items);
echo json_encode([
    'success' => true,
    'message' => 'Đã thêm vào giỏ hàng!',
    'cart' => cart_items_to_map($items),
    'items' => $items,
    'summary' => $summary
]);
?>
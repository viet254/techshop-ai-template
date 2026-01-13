<?php
// Lịch sử đơn hàng của người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}
$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.total,
        o.discount,
        o.final_total,
        o.voucher_code,
        o.status,
        o.created_at,
        /* Lấy tên sản phẩm đầu tiên trong đơn */
        (
            SELECT p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = o.id 
            LIMIT 1
        ) AS first_product_name,
        /* Lấy ảnh sản phẩm đầu tiên trong đơn */
        (
            SELECT p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = o.id 
            LIMIT 1
        ) AS first_product_image,
        /* Tổng số sản phẩm trong đơn (tính theo quantity) */
        (
            SELECT SUM(oi.quantity) 
            FROM order_items oi 
            WHERE oi.order_id = o.id
        ) AS items_count
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'total' => (float)$row['total'],
        'discount' => (float)$row['discount'],
        'final_total' => (float)$row['final_total'],
        'voucher_code' => $row['voucher_code'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'first_product_name' => $row['first_product_name'],
        'first_product_image' => $row['first_product_image'],
        'items_count' => isset($row['items_count']) ? (int)$row['items_count'] : null
    ];
}
echo json_encode($orders);
?>
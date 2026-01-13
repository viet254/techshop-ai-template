<?php
// Lấy tất cả đơn hàng cho admin
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    echo json_encode([]);
    exit;
}
$sql = "
    SELECT 
        o.id,
        o.user_id,
        o.total,
        o.discount,
        o.final_total,
        o.voucher_code,
        o.address_id,
        o.status,
        o.created_at,
        u.username,
        (
            SELECT p.name
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = o.id 
            LIMIT 1
        ) AS first_product_name,
        (
            SELECT p.image
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = o.id 
            LIMIT 1
        ) AS first_product_image,
        (
            SELECT SUM(oi.quantity)
            FROM order_items oi 
            WHERE oi.order_id = o.id
        ) AS items_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql);
$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ép kiểu cho các trường số
        $row['total'] = (float)$row['total'];
        $row['discount'] = (float)$row['discount'];
        $row['final_total'] = (float)$row['final_total'];
        if (isset($row['items_count'])) {
            $row['items_count'] = (int)$row['items_count'];
        }
        $orders[] = $row;
    }
}
echo json_encode($orders);
?>
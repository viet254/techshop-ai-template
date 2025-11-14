<?php
// API: Trả về số liệu thống kê cho admin dashboard
// Chỉ cho phép admin gọi
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../database/connect.php';

// Hàm an toàn để lấy số lượng từ bảng
function count_table($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
    return 0;
}

$stats = [];

// Tổng số sản phẩm
$stats['total_products'] = count_table($conn, 'products');

// Tổng số người dùng
$stats['total_users'] = count_table($conn, 'users');

// Thống kê số đơn hàng theo trạng thái và doanh thu
$statuses = ['pending' => 0, 'processing' => 0, 'shipping' => 0, 'completed' => 0, 'cancelled' => 0];
$total_revenue = 0;
$result = $conn->query("SELECT status, final_total FROM orders");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        $total = (float)$row['final_total'];
        if (isset($statuses[$status])) {
            $statuses[$status]++;
        }
        // Chỉ cộng doanh thu đơn hàng đã hoàn thành
        if ($status === 'completed') {
            $total_revenue += $total;
        }
    }
}

$stats['orders'] = $statuses;
$stats['revenue'] = $total_revenue;

echo json_encode(['success' => true, 'stats' => $stats]);
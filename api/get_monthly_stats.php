<?php
// API: trả về thống kê số đơn và doanh thu theo từng tháng trong năm
session_start();
header('Content-Type: application/json');
// Chỉ cho phép admin truy cập
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../database/connect.php';

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Lấy dữ liệu đơn hàng đã hoàn thành trong năm
$stmt = $conn->prepare("SELECT MONTH(created_at) AS month, COUNT(*) AS orders, SUM(final_total) AS revenue FROM orders WHERE status = 'Completed' AND YEAR(created_at) = ? GROUP BY MONTH(created_at)");
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

// Khởi tạo mảng 12 tháng với giá trị 0
$months = [];
$orders = [];
$revenues = [];
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = 0;
    $orders[$i] = 0;
    $revenues[$i] = 0;
}

while ($row = $result->fetch_assoc()) {
    $m = (int)$row['month'];
    $orders[$m] = (int)$row['orders'];
    $revenues[$m] = (float)$row['revenue'];
}

// Chuyển về mảng tuần tự
$months_out = [];
$orders_out = [];
$revenues_out = [];
for ($i = 1; $i <= 12; $i++) {
    $months_out[] = $i;
    $orders_out[] = $orders[$i];
    $revenues_out[] = $revenues[$i];
}

echo json_encode([
    'success' => true,
    'year' => $year,
    'months' => $months_out,
    'orders' => $orders_out,
    'revenues' => $revenues_out
]);
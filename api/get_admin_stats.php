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

// Hàm an toàn để lấy số lượng hoặc tổng từ bảng
/**
 * Đếm số bản ghi trong một bảng.
 *
 * @param mysqli $conn Kết nối tới cơ sở dữ liệu
 * @param string $table Tên bảng cần đếm
 * @return int Số bản ghi
 */
function count_table($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
    return 0;
}

/**
 * Tính tổng một cột số trong bảng.
 *
 * @param mysqli $conn Kết nối tới cơ sở dữ liệu
 * @param string $table Tên bảng
 * @param string $column Cột cần tính tổng
 * @return float Tổng của cột hoặc 0 nếu lỗi
 */
function sum_column($conn, $table, $column) {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $result = $conn->query("SELECT SUM($column) AS total FROM `$table`");
    if ($result) {
        $row = $result->fetch_assoc();
        return (float)($row['total'] ?? 0);
    }
    return 0;
}

$stats = [];

// Tổng số sản phẩm: đếm số dòng trong bảng products (số sản phẩm khác nhau)
$stats['total_products'] = count_table($conn, 'products');

// Tổng số người dùng
$stats['total_users'] = count_table($conn, 'users');

// Thống kê số đơn hàng theo trạng thái và doanh thu
// Khởi tạo thống kê đơn hàng theo các trạng thái (dùng key chữ thường)
$statuses = ['pending' => 0, 'processing' => 0, 'shipping' => 0, 'completed' => 0, 'cancelled' => 0];
$total_revenue = 0;
// Lấy danh sách trạng thái và tổng tiền của tất cả đơn hàng
$result = $conn->query("SELECT status, final_total FROM orders");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Chuẩn hóa trạng thái về chữ thường để so sánh, do một số cột có thể lưu hoa/thường khác nhau
        $status = strtolower(trim($row['status']));
        $total  = (float)$row['final_total'];
        if (isset($statuses[$status])) {
            $statuses[$status]++;
        }
        // Cộng doanh thu cho những đơn hàng đã hoàn thành
        if ($status === 'completed') {
            $total_revenue += $total;
        }
    }
}

$stats['orders'] = $statuses;
$stats['revenue'] = $total_revenue;

echo json_encode(['success' => true, 'stats' => $stats]);
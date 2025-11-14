<?php
// Endpoint to cancel an order by user
session_start();
header('Content-Type: application/json');

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$reason = isset($data['reason']) ? trim($data['reason']) : '';

if (!$order_id || $reason === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu thông tin cần thiết']);
    exit;
}

require_once __DIR__ . '/../database/connect.php';

$user_id = $_SESSION['user_id'];

// Kiểm tra đơn hàng thuộc về người dùng và trạng thái cho phép hủy
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy đơn hàng của bạn']);
    exit;
}
$order = $result->fetch_assoc();

$allowedStatuses = ['Pending', 'Processing', 'Shipping'];
if (!in_array($order['status'], $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Đơn hàng không thể hủy ở trạng thái hiện tại.']);
    exit;
}

// Bắt đầu transaction
$conn->begin_transaction();
try {
    // Cập nhật trạng thái đơn hàng và lưu lý do hủy
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled', cancel_reason = ? WHERE id = ?");
    $stmt->bind_param('si', $reason, $order_id);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi khi cập nhật trạng thái đơn hàng');
    }
    // Khôi phục tồn kho cho các sản phẩm trong đơn
    $itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $itemsStmt->bind_param('i', $order_id);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    while ($row = $itemsResult->fetch_assoc()) {
        $updateStockStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $updateStockStmt->bind_param('ii', $row['quantity'], $row['product_id']);
        $updateStockStmt->execute();
    }
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được hủy']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}

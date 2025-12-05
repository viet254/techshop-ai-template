<?php
// API: Xóa đơn hàng (chỉ dành cho admin)
// Phương thức: POST, truyền JSON { id: <order_id> }
// Khi xóa sẽ hoàn lại tồn kho và xóa cả dòng trong bảng order_items

session_start();
header('Content-Type: application/json');

// Chỉ cho phép admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền']);
    exit;
}

// Chỉ xử lý phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$orderId = isset($data['id']) ? (int)$data['id'] : 0;
if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../database/connect.php';

// Kiểm tra đơn hàng có tồn tại hay không
$checkStmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
if (!$checkStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
    exit;
}
$checkStmt->bind_param('i', $orderId);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows === 0) {
    $checkStmt->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
    exit;
}
$checkStmt->close();

// Bắt đầu transaction
$conn->autocommit(false);

// Lấy các item để hoàn lại tồn kho
$itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
if (!$itemsStmt) {
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
    exit;
}
$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();
$itemsStmt->bind_result($pId, $qty);
$items = [];
while ($itemsStmt->fetch()) {
    $items[] = ['product_id' => (int)$pId, 'quantity' => (int)$qty];
}
$itemsStmt->close();

// Hoàn lại tồn kho
foreach ($items as $item) {
    $q = $item['quantity'];
    $pid = $item['product_id'];
    if ($q <= 0) continue;
    $stockStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    if (!$stockStmt) {
        $conn->rollback();
        $conn->autocommit(true);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        exit;
    }
    $stockStmt->bind_param('ii', $q, $pid);
    if (!$stockStmt->execute()) {
        $stockStmt->close();
        $conn->rollback();
        $conn->autocommit(true);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật tồn kho']);
        exit;
    }
    $stockStmt->close();
}

// Xóa order_items
$delItems = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
if (!$delItems) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
    exit;
}
$delItems->bind_param('i', $orderId);
if (!$delItems->execute()) {
    $delItems->close();
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể xóa chi tiết đơn hàng']);
    exit;
}
$delItems->close();

// Xóa order
$delOrder = $conn->prepare("DELETE FROM orders WHERE id = ?");
if (!$delOrder) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
    exit;
}
$delOrder->bind_param('i', $orderId);
if (!$delOrder->execute()) {
    $delOrder->close();
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể xóa đơn hàng']);
    exit;
}
$delOrder->close();

// Commit
if (!$conn->commit()) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể hoàn thành thao tác']);
    exit;
}

$conn->autocommit(true);

// Trả về thành công
echo json_encode(['success' => true]);
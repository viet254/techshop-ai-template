<?php
// API hủy đơn hàng của người dùng
session_start();
header('Content-Type: application/json');

// Chỉ cho phép phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Kiểm tra đăng nhập: login.php lưu thông tin vào $_SESSION['user']
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

// Đọc JSON từ body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu mã đơn hàng']);
    exit;
}

$order_id = (int)$input['order_id'];
$reason   = isset($input['reason']) ? trim($input['reason']) : '';

if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Mã đơn hàng không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../database/connect.php';

// --- 1. Kiểm tra đơn hàng có thuộc về user và trạng thái có thể hủy không ---
$sql = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$checkStmt = $conn->prepare($sql);
if (!$checkStmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi hệ thống: ' . $conn->error]);
    exit;
}

$checkStmt->bind_param('ii', $order_id, $user_id);
$checkStmt->execute();
$checkStmt->bind_result($status);
if (!$checkStmt->fetch()) {
    // Không tìm thấy đơn hoặc không thuộc user
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.']);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Nếu đã hoàn thành hoặc đã hủy rồi thì không cho hủy nữa
if ($status === 'Completed') {
    http_response_code(400);
    echo json_encode(['error' => 'Đơn hàng đã hoàn thành, không thể hủy.']);
    exit;
}
if ($status === 'Cancelled') {
    http_response_code(400);
    echo json_encode(['error' => 'Đơn hàng này đã được hủy trước đó.']);
    exit;
}

// --- 2. Bắt đầu transaction để cập nhật trạng thái + hoàn lại tồn kho ---
$conn->autocommit(false);

// 2.1. Cập nhật trạng thái đơn hàng và lưu lý do hủy nếu có cột cancel_reason
// Nếu cột cancel_reason tồn tại, câu lệnh UPDATE sẽ bao gồm cancel_reason. Nếu không, vẫn chỉ cập nhật status.
$updateQuery = "UPDATE orders SET status = 'Cancelled', cancel_reason = ? WHERE id = ?";
$upd = $conn->prepare($updateQuery);
if (!$upd) {
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi hệ thống: ' . $conn->error]);
    exit;
}
// Sử dụng kiểu dữ liệu string cho lý do và integer cho id
$upd->bind_param('si', $reason, $order_id);
if (!$upd->execute()) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi khi cập nhật trạng thái đơn hàng.']);
    $upd->close();
    exit;
}
$upd->close();

// 2.2. Lấy danh sách sản phẩm trong đơn để hoàn lại tồn kho
$itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
if (!$itemsStmt) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi hệ thống: ' . $conn->error]);
    exit;
}
$itemsStmt->bind_param('i', $order_id);
$itemsStmt->execute();
$itemsStmt->bind_result($product_id, $qty);

$items = [];
while ($itemsStmt->fetch()) {
    $items[] = ['product_id' => (int)$product_id, 'quantity' => (int)$qty];
}
$itemsStmt->close();

// 2.3. Hoàn lại tồn kho từng sản phẩm
foreach ($items as $item) {
    $pId = $item['product_id'];
    $q   = $item['quantity'];
    if ($q <= 0) continue;

    $stockStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    if (!$stockStmt) {
        $conn->rollback();
        $conn->autocommit(true);
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi hệ thống: ' . $conn->error]);
        exit;
    }
    $stockStmt->bind_param('ii', $q, $pId);
    if (!$stockStmt->execute()) {
        $stockStmt->close();
        $conn->rollback();
        $conn->autocommit(true);
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi khi cập nhật tồn kho.']);
        exit;
    }
    $stockStmt->close();
}

// Nếu mọi thứ OK thì commit
if (!$conn->commit()) {
    $conn->rollback();
    $conn->autocommit(true);
    http_response_code(500);
    echo json_encode(['error' => 'Không thể hoàn tất thao tác hủy đơn hàng.']);
    exit;
}

$conn->autocommit(true);

// Thành công
echo json_encode([
    'success' => true,
    'message' => 'Đơn hàng đã được hủy thành công.'
]);

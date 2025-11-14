<?php
// Thêm đánh giá cho sản phẩm
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$rating = isset($data['rating']) ? (int)$data['rating'] : 0;
$comment = trim($data['comment'] ?? '');
if ($productId <= 0 || $rating < 1 || $rating > 5 || !$comment) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá']);
    exit;
}
$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiis', $productId, $userId, $rating, $comment);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể thêm đánh giá']);
}
?>
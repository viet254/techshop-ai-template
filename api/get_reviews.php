<?php
// Lấy đánh giá cho sản phẩm
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$stmt = $conn->prepare("SELECT r.rating, r.comment, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param('i', $productId);
$stmt->execute();
$res = $stmt->get_result();
$reviews = [];
while ($row = $res->fetch_assoc()) {
    $reviews[] = [
        'rating' => (int)$row['rating'],
        'comment' => $row['comment'],
        'username' => $row['username'] ? $row['username'] : 'Ẩn danh'
    ];
}
echo json_encode($reviews);
?>
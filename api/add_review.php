<?php
// Thêm đánh giá cho sản phẩm
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$rating = isset($data['rating']) ? (int)$data['rating'] : 0;
$comment = trim($data['comment'] ?? '');

// Basic validation: rating must be within 1-5; ensure productId is positive. Comment may be optional if the
// database does not support it. We still accept empty comment but treat it as empty string.
if ($productId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Require login to add a review
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Determine if the reviews table has a comment column
$hasComment = false;
$colRes = $conn->query('SHOW COLUMNS FROM reviews');
if ($colRes) {
    while ($row = $colRes->fetch_assoc()) {
        if (isset($row['Field']) && $row['Field'] === 'comment') {
            $hasComment = true;
            break;
        }
    }
    $colRes->free();
}

// Prepare insert statement based on schema
if ($hasComment) {
    // Ensure comment is not null (use empty string if omitted)
    $cmt = $comment !== '' ? $comment : '';
    $stmt = $conn->prepare('INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiis', $productId, $userId, $rating, $cmt);
} else {
    // Insert without comment column
    $stmt = $conn->prepare('INSERT INTO reviews (product_id, user_id, rating) VALUES (?, ?, ?)');
    $stmt->bind_param('iii', $productId, $userId, $rating);
}

if ($stmt && $stmt->execute()) {
    echo json_encode(['success' => true]);
    $stmt->close();
} else {
    // Provide generic error to avoid exposing DB schema details
    echo json_encode(['success' => false, 'message' => 'Không thể thêm đánh giá']);
}
?>
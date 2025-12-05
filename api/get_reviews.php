<?php
// Lấy đánh giá cho sản phẩm
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
// Ensure product ID is an integer
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

/**
 * Fetch the column names from the reviews table. This helper allows the
 * endpoint to adapt when the schema differs between deployments (e.g. legacy
 * tables that lack a comment or created_at column). It returns an
 * associative array mapping column names to true.
 *
 * @param mysqli $conn The active database connection
 * @return array<string,bool> A map of column names
 */
function getReviewColumns(mysqli $conn): array {
    $cols = [];
    $result = $conn->query('SHOW COLUMNS FROM reviews');
    if ($result) {
        while ($r = $result->fetch_assoc()) {
            if (isset($r['Field'])) {
                $cols[$r['Field']] = true;
            }
        }
        $result->free();
    }
    return $cols;
}

$cols = getReviewColumns($conn);
// Always select the rating column
$selectFields = 'r.rating';
// Include comment column if it exists; otherwise alias an empty string
if (isset($cols['comment'])) {
    $selectFields .= ', r.comment';
} else {
    $selectFields .= ", '' AS comment";
}
// Determine ordering: prefer created_at, fallback to id
$orderBy = 'r.id DESC';
if (isset($cols['created_at'])) {
    $orderBy = 'r.created_at DESC';
}

// Build the dynamic SQL using the selected columns and order
$sql = "SELECT $selectFields, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY $orderBy";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $res = $stmt->get_result();
    $reviews = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $reviews[] = [
                'rating'   => isset($row['rating']) ? (int)$row['rating'] : 0,
                'comment'  => isset($row['comment']) ? $row['comment'] : '',
                'username' => (isset($row['username']) && $row['username']) ? $row['username'] : 'Ẩn danh'
            ];
        }
        $res->free();
    }
    $stmt->close();
    echo json_encode($reviews);
    return;
}

// If preparing the dynamic SQL failed (unlikely), fall back to a simplified query
$fallback = $conn->prepare('SELECT r.rating, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC');
if ($fallback) {
    $fallback->bind_param('i', $productId);
    $fallback->execute();
    $res = $fallback->get_result();
    $reviews = [];
    while ($row = $res->fetch_assoc()) {
        $reviews[] = [
            'rating'   => isset($row['rating']) ? (int)$row['rating'] : 0,
            'comment'  => '',
            'username' => (isset($row['username']) && $row['username']) ? $row['username'] : 'Ẩn danh'
        ];
    }
    $fallback->close();
    echo json_encode($reviews);
    return;
}
// Ultimate fallback: return empty JSON array
echo json_encode([]);
?>
<?php
// Lấy danh sách sản phẩm liên quan cho trang chi tiết sản phẩm
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';

// Nhận ID sản phẩm và giới hạn số lượng sản phẩm liên quan muốn lấy
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
if ($limit <= 0) {
    $limit = 4;
}

// Trả về mảng rỗng nếu ID sản phẩm không hợp lệ
if ($productId <= 0) {
    echo json_encode([]);
    exit;
}

// Lấy danh mục của sản phẩm hiện tại
$stmt = $conn->prepare('SELECT category FROM products WHERE id = ?');
if (!$stmt) {
    echo json_encode([]);
    exit;
}
$stmt->bind_param('i', $productId);
$stmt->execute();
$res = $stmt->get_result();
$category = null;
if ($res) {
    $row = $res->fetch_assoc();
    if ($row) {
        $category = $row['category'];
    }
    $res->free();
}
$stmt->close();

if (!$category) {
    // Không xác định được danh mục, trả về rỗng
    echo json_encode([]);
    exit;
}

// Lấy các sản phẩm khác cùng danh mục, loại trừ sản phẩm hiện tại
$stmt2 = $conn->prepare('SELECT id, name, price, image FROM products WHERE category = ? AND id <> ? LIMIT ?');
if (!$stmt2) {
    echo json_encode([]);
    exit;
}
$stmt2->bind_param('sii', $category, $productId, $limit);
$stmt2->execute();
$res2 = $stmt2->get_result();
$products = [];
if ($res2) {
    while ($p = $res2->fetch_assoc()) {
        $products[] = [
            'id'    => (int)$p['id'],
            'name'  => $p['name'],
            'price' => (float)$p['price'],
            'image' => $p['image']
        ];
    }
    $res2->free();
}
$stmt2->close();
echo json_encode($products);
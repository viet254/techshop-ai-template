<?php
// API đăng nhập
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
    exit;
}
// Tìm kiếm bằng username hoặc email
$stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if ($user && password_verify($password, $user['password'])) {
    // Đăng nhập thành công
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    $userId = (int)$user['id'];
    // Hợp nhất giỏ hàng hiện có trong session với bảng cart_items
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $pid => $item) {
            $pid = (int)$pid;
            $qty = (int)$item['quantity'];
            // Kiểm tra tồn tại
            $stmtCart = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmtCart->bind_param('ii', $userId, $pid);
            $stmtCart->execute();
            $resCart = $stmtCart->get_result();
            if ($row = $resCart->fetch_assoc()) {
                $newQty = $row['quantity'] + $qty;
                $updateCart = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $updateCart->bind_param('ii', $newQty, $row['id']);
                $updateCart->execute();
            } else {
                $insertCart = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insertCart->bind_param('iii', $userId, $pid, $qty);
                $insertCart->execute();
            }
        }
        // Xóa giỏ hàng trong session sau khi hợp nhất
        unset($_SESSION['cart']);
    }
    // Hợp nhất saved items hiện có trong session với bảng saved_items
    if (!empty($_SESSION['saved'])) {
        foreach ($_SESSION['saved'] as $pid => $item) {
            $pid = (int)$pid;
            $qty = (int)$item['quantity'];
            $stmtSaved = $conn->prepare("SELECT id, quantity FROM saved_items WHERE user_id = ? AND product_id = ?");
            $stmtSaved->bind_param('ii', $userId, $pid);
            $stmtSaved->execute();
            $resSaved = $stmtSaved->get_result();
            if ($row = $resSaved->fetch_assoc()) {
                $newQty = $row['quantity'] + $qty;
                $updateSaved = $conn->prepare("UPDATE saved_items SET quantity = ? WHERE id = ?");
                $updateSaved->bind_param('ii', $newQty, $row['id']);
                $updateSaved->execute();
            } else {
                $insertSaved = $conn->prepare("INSERT INTO saved_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insertSaved->bind_param('iii', $userId, $pid, $qty);
                $insertSaved->execute();
            }
        }
        unset($_SESSION['saved']);
    }
    echo json_encode(['success' => true, 'role' => $user['role']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập/email hoặc mật khẩu sai']);
}
?>
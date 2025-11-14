<?php
// Tải lên ảnh đại diện cho người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập']);
    exit;
}
// Kiểm tra xem có file gửi lên không
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không có file tải lên']);
    exit;
}
$file = $_FILES['avatar'];
// Giới hạn loại file
$allowed = ['image/jpeg', 'image/jpg', 'image/png'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Định dạng file không hợp lệ']);
    exit;
}
// Tạo tên file mới duy nhất
$userId = (int)$_SESSION['user']['id'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName = 'user_' . $userId . '_' . time() . '.' . $ext;
$targetDir = __DIR__ . '/../assets/images/avatars/';
// Tạo thư mục nếu chưa có
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}
$targetPath = $targetDir . $newName;
// Di chuyển file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
    exit;
}
// Lưu đường dẫn vào DB (lưu tên file, không lưu đường dẫn đầy đủ)
$stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
$stmt->bind_param('si', $newName, $userId);
$stmt->execute();
// Cập nhật session
$_SESSION['user']['avatar'] = $newName;
echo json_encode(['success' => true, 'avatar' => $newName]);
?>
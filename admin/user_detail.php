<?php
// Trang xem chi tiết thông tin người dùng cho admin
// Kiểm tra quyền admin và in header
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';

// Lấy ID người dùng từ query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    echo '<h2>Thông tin người dùng</h2>';
    echo '<p>Người dùng không hợp lệ.</p>';
    echo '</main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Truy vấn thông tin người dùng
$stmt = $conn->prepare("SELECT id, username, email, phone, role, avatar, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo '<h2>Thông tin người dùng #' . htmlspecialchars($userId) . '</h2>';
echo '<div class="admin-card">';
if (!$user) {
    echo '<p>Không tìm thấy người dùng.</p>';
} else {
    // Xác định đường dẫn avatar. Nếu không có, dùng avatar mặc định
    $avatarFile = $user['avatar'] ?? '';
    $avatarPath = '/assets/images/default-avatar.png';
    if ($avatarFile) {
        $avatarPath = '/assets/images/avatars/' . $avatarFile;
    }
    echo '<div style="display:flex; gap:20px; align-items:flex-start;">';
    echo '<img src="' . htmlspecialchars($avatarPath) . '" alt="Avatar" style="width:120px; height:120px; object-fit:cover; border-radius:8px;">';
    echo '<div>';
    echo '<p><strong>ID:</strong> ' . $user['id'] . '</p>';
    echo '<p><strong>Tên đăng nhập:</strong> ' . htmlspecialchars($user['username']) . '</p>';
    echo '<p><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</p>';
    echo '<p><strong>Điện thoại:</strong> ' . htmlspecialchars($user['phone'] ?? '') . '</p>';
    echo '<p><strong>Quyền:</strong> ' . htmlspecialchars($user['role']) . '</p>';
    echo '<p><strong>Ngày tạo:</strong> ' . htmlspecialchars($user['created_at']) . '</p>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';
echo '</main>';
include __DIR__ . '/../includes/footer.php';
?>
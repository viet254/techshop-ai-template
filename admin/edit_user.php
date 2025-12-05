<?php
// Trang chỉnh sửa thông tin người dùng cho admin
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';

// Lấy ID người dùng từ query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    echo '<h2>Chỉnh sửa người dùng</h2>';
    echo '<p>Người dùng không hợp lệ.</p>';
    echo '</main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Không cho phép chỉnh sửa chính bản thân qua trang này
if ($userId == ($_SESSION['user']['id'] ?? 0)) {
    echo '<h2>Chỉnh sửa người dùng</h2>';
    echo '<p>Bạn không thể chỉnh sửa thông tin của chính mình tại đây.</p>';
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

echo '<h2>Chỉnh sửa người dùng #' . htmlspecialchars($userId) . '</h2>';

if (!$user) {
    echo '<div class="admin-card"><p>Không tìm thấy người dùng.</p></div>';
    echo '</main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Xác định đường dẫn avatar hiện tại
$avatarFile = $user['avatar'] ?? '';
$avatarPath = '/assets/images/default-avatar.png';
if ($avatarFile) {
    $avatarPath = '/assets/images/avatars/' . $avatarFile;
}
?>
<div class="admin-card">
    <div style="display:flex; gap:20px; align-items:flex-start; margin-bottom:20px;">
        <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" style="width:120px; height:120px; object-fit:cover; border-radius:8px;">
        <div>
            <p><strong>ID:</strong> <?= $user['id'] ?></p>
            <p><strong>Quyền:</strong> <?= htmlspecialchars($user['role']) ?></p>
            <p><strong>Ngày tạo:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        </div>
    </div>
    <form action="update_user.php" method="post" class="admin-form" style="max-width:400px;">
        <input type="hidden" name="id" value="<?= $user['id'] ?>" />
        <label>Tên đăng nhập:
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required />
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
        </label>
        <label>Điện thoại:
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
        </label>
        <label>Mật khẩu mới (để trống nếu không đổi):
            <input type="password" name="password" />
        </label>
        <button type="submit" class="btn-edit"><span class="icon">💾</span> Lưu</button>
    </form>
</div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
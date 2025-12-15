<?php
// Trang xem chi tiết thông tin người dùng cho admin
// Kiểm tra quyền admin và in header
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';

// Lấy ID người dùng từ query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    echo '<div class="alert alert-warning">Người dùng không hợp lệ.</div>';
    include __DIR__ . '/../includes/admin_footer.php';
    exit;
}

// Truy vấn thông tin người dùng
$stmt = $conn->prepare("SELECT id, username, email, phone, role, avatar, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-id text-primary"></i>
            </div>
            <div>
                Thông tin người dùng #<?= htmlspecialchars($userId) ?>
                <div class="page-title-subheading">Chi tiết liên hệ và quyền hạn.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a class="btn btn-outline-secondary btn-shadow" href="/admin/manage_users.php">Quay lại danh sách</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!$user): ?>
            <p class="mb-0">Không tìm thấy người dùng.</p>
        <?php else: ?>
            <?php
                $avatarFile = $user['avatar'] ?? '';
                $avatarPath = '/assets/images/default-avatar.png';
                if ($avatarFile) {
                    $avatarPath = '/assets/images/avatars/' . $avatarFile;
                }
            ?>
            <div class="d-flex align-items-start gap-3">
                <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" style="width:120px; height:120px; object-fit:cover; border-radius:8px;">
                <div>
                    <p class="mb-1"><strong>ID:</strong> <?= $user['id'] ?></p>
                    <p class="mb-1"><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p class="mb-1"><strong>Điện thoại:</strong> <?= htmlspecialchars($user['phone'] ?? '') ?></p>
                    <p class="mb-1"><strong>Quyền:</strong> <?= htmlspecialchars($user['role']) ?></p>
                    <p class="mb-1"><strong>Ngày tạo:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
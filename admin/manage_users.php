<?php
// Quản lý người dùng (admin)
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';
// Lấy danh sách người dùng
$result = $conn->query("SELECT id, username, email, phone, role FROM users ORDER BY id DESC");
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users text-primary"></i>
            </div>
            <div>
                Quản lý người dùng
                <div class="page-title-subheading">Phân quyền, chỉnh sửa thông tin và xem chi tiết tài khoản.</div>
            </div>
        </div>
    </div>
</div>
<?php
// Hiển thị thông báo nếu có tham số msg trong query string
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    echo "<script>document.addEventListener('DOMContentLoaded', function(){ showNotification('Đã cập nhật thông tin người dùng', 'success'); });</script>";
}
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle admin-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tài khoản</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Quyền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td>
                            <span class="badge-soft <?= $u['role'] === 'admin' ? 'badge-soft-info' : 'badge-soft-warning' ?>">
                                <?= htmlspecialchars($u['role']) ?>
                            </span>
                        </td>
                        <td style="min-width:220px;">
                            <?php if ($u['id'] == ($_SESSION['user']['id'] ?? 0)): ?>
                                <span class="text-muted">(Bạn)</span>
                            <?php else: ?>
                                <form action="update_user_role.php" method="post" class="role-form mb-2 d-flex align-items-center gap-2">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>" />
                                    <select name="role" class="custom-select custom-select-sm w-auto">
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn-edit btn btn-sm" title="Cập nhật quyền"><span class="icon">🔄</span> Lưu</button>
                                </form>
                                <a href="/admin/edit_user.php?id=<?= $u['id'] ?>" class="btn-edit btn btn-sm d-inline-flex align-items-center mb-1"><span class="icon">✏️</span> Sửa</a>
                            <?php endif; ?>
                            <a href="/admin/user_detail.php?id=<?= $u['id'] ?>" class="btn btn-light btn-sm d-inline-flex align-items-center"><span class="icon">ℹ️</span> Xem</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
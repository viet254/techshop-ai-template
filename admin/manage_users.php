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
    <h2>Quản lý người dùng</h2>
    <?php
    // Hiển thị thông báo nếu có tham số msg trong query string
    if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ showNotification('Đã cập nhật thông tin người dùng', 'success'); });</script>";
    }
    ?>
    <div class="admin-card">
        <table class="admin-table">
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
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td style="min-width:200px;">
                        <?php if ($u['id'] == ($_SESSION['user']['id'] ?? 0)): ?>
                            (Bạn)
                        <?php else: ?>
                            <form action="update_user_role.php" method="post" class="role-form" style="display:flex; gap:5px; align-items:center; margin-bottom:5px;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>" />
                                <select name="role">
                                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn-edit" title="Cập nhật quyền"><span class="icon">🔄</span> Lưu</button>
                            </form>
                            <!-- Liên kết chỉnh sửa thông tin người dùng -->
                            <a href="/admin/edit_user.php?id=<?= $u['id'] ?>" class="btn-edit" style="margin-top:5px; display:inline-flex; align-items:center;"><span class="icon">✏️</span> Sửa</a>
                        <?php endif; ?>
                        <!-- Liên kết xem chi tiết người dùng -->
                        <a href="/admin/user_detail.php?id=<?= $u['id'] ?>" class="btn-edit" style="margin-top:5px; display:inline-flex; align-items:center;"><span class="icon">ℹ️</span> Xem</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
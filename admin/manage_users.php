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
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr><th>ID</th><th>Tài khoản</th><th>Email</th><th>Điện thoại</th><th>Quyền</th><th>Hành động</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <!-- Nếu là chính bạn thì không cho đổi quyền -->
                        <?php if ($u['id'] == ($_SESSION['user']['id'] ?? 0)): ?>
                            (Bạn)
                        <?php else: ?>
                            <form action="update_user_role.php" method="post" style="display:flex; gap:5px; align-items:center;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>" />
                                <select name="role">
                                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit">Cập nhật</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
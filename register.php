<?php
// Trang đăng ký
include __DIR__ . '/includes/header.php';
// Nếu đã đăng nhập thì chuyển tới trang cá nhân
if (isset($_SESSION['user'])) {
    // Nếu đã đăng nhập, chuyển hướng tùy theo role
    if (($_SESSION['user']['role'] ?? 'user') === 'admin') {
        header('Location: /techshop-ai-template/admin/dashboard.php');
    } else {
        header('Location: /techshop-ai-template/index.php');
    }
    exit;
}
?>
<main class="main-content register-page">
    <div class="auth-card">
        <h2>Đăng ký tài khoản</h2>
        <form id="register-form" method="post">
            <label>Tên đăng nhập:
                <input type="text" id="reg-username" name="username" required />
            </label>
            <label>Email:
                <input type="email" id="reg-email" name="email" required />
            </label>
            <label>Mật khẩu:
                <input type="password" id="reg-password" name="password" required />
            </label>
            <label>Xác nhận mật khẩu:
                <input type="password" id="reg-confirm-password" name="confirm_password" required />
            </label>
            <button type="submit">Đăng ký</button>
        </form>
        <p>Bạn đã có tài khoản? <a href="/techshop-ai-template/login.php">Đăng nhập</a>.</p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/register.js" defer></script>
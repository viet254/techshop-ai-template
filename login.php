<?php
// Trang đăng nhập
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
<main class="main-content login-page">
    <div class="auth-card">
        <h2>Đăng nhập</h2>
        <form id="login-form" method="post">
            <label for="login-username">Tên đăng nhập hoặc Email:</label>
            <input type="text" id="login-username" name="username" required />

            <label for="login-password">Mật khẩu:</label>
            <input type="password" id="login-password" name="password" required />

            <button type="submit">Đăng nhập</button>
        </form>
        <p>Bạn chưa có tài khoản? <a href="/techshop-ai-template/register.php">Đăng ký ngay</a>.</p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/login.js" defer></script>
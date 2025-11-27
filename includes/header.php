<?php
// Global header include
// Start the session and set up some helper variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && ($_SESSION['user']['role'] ?? 'user') === 'admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>TechShop AI</title>
    <link rel="stylesheet" href="/techshop-ai-template/assets/css/style.css">
    <!-- Estore theme overrides: load after main stylesheet -->
    <link rel="stylesheet" href="/techshop-ai-template/assets/css/estore.css">
    <script src="/techshop-ai-template/assets/js/chatbox.js" defer></script>
</head>
<body>
<header class="header">
    <div class="logo">
        <a href="/techshop-ai-template/index.php">TechShop <span>AI</span></a>
    </div>
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Bạn cần tìm gì?" />
        <button id="searchBtn" class="search-btn">🔍</button>
    </div>
    <div class="user-cart">
        <div class="user-menu">
            <span class="user-icon">👤</span>
            <?php if ($isLoggedIn): ?>
                <span class="username"><?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?></span>
                <div class="user-dropdown">
                    <a href="/techshop-ai-template/profile.php">Thông tin tài khoản</a>
                    <?php if ($isAdmin): ?>
                        <a href="/techshop-ai-template/admin/dashboard.php">Quản trị</a>
                    <?php endif; ?>
                    <a href="/techshop-ai-template/api/logout.php">Đăng xuất</a>
                </div>
            <?php else: ?>
                <div class="user-dropdown">
                    <a href="/techshop-ai-template/login.php">Đăng nhập</a>
                    <a href="/techshop-ai-template/register.php">Đăng ký</a>
                </div>
            <?php endif; ?>
        </div>
        <!-- Giỏ hàng được chuyển xuống thanh điều hướng -->
    </div>
</header>

<nav class="main-nav">
    <a href="/techshop-ai-template/index.php">Trang chủ</a>
    <a href="/techshop-ai-template/products.php">Sản phẩm</a>
    <!-- Link tới trang Đơn hàng của tôi; yêu cầu đăng nhập -->
    <a href="/techshop-ai-template/orders.php" class="protected-link">Đơn hàng</a>
    <!-- Link giỏ hàng được chuyển xuống thanh điều hướng -->
    <a href="/techshop-ai-template/cart.php" class="protected-link">Giỏ hàng <span id="nav-cart-count"></span></a>
</nav>
<script>
// Biến login từ PHP sang JS
const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
// Truyền ID người dùng (nếu có) sang JS để lưu lịch sử chat
const USER_ID = <?php echo $isLoggedIn ? ((int)($_SESSION['user']['id'] ?? 0)) : 'null'; ?>;
// Cập nhật số lượng giỏ hàng trên header và xử lý tìm kiếm, bảo vệ liên kết
document.addEventListener('DOMContentLoaded', function () {
    // Update cart count
    fetch('/techshop-ai-template/api/get_cart.php')
        .then(res => res.json())
        .then(data => {
            const count = Object.keys(data.cart || {}).length;
            // Cập nhật số lượng trên biểu tượng giỏ (nếu còn trong header) và trên thanh nav
            const cartCount = document.getElementById('cart-count');
            const navCartCount = document.getElementById('nav-cart-count');
            const text = count > 0 ? '(' + count + ')' : '';
            if (cartCount) cartCount.textContent = text;
            if (navCartCount) navCartCount.textContent = text;
        })
        .catch(() => {});
    // Search on Enter or button click
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    function doSearch() {
        const q = searchInput.value.trim();
        if (q) {
            window.location.href = '/techshop-ai-template/products.php?q=' + encodeURIComponent(q);
        }
    }
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                doSearch();
            }
        });
    }
    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            doSearch();
        });
    }
    // Protect links that require login
    document.querySelectorAll('.protected-link').forEach(link => {
        link.addEventListener('click', function (e) {
            if (!IS_LOGGED_IN) {
                e.preventDefault();
                window.location.href = '/techshop-ai-template/login.php';
            }
        });
    });

    // Xử lý hiệu ứng cuộn: phóng to logo và thanh tìm kiếm khi cuộn
    const headerEl = document.querySelector('.header');
    function handleScroll() {
        if (window.scrollY > 50) {
            headerEl.classList.add('scrolled');
        } else {
            headerEl.classList.remove('scrolled');
        }
    }
    window.addEventListener('scroll', handleScroll);
});
</script>
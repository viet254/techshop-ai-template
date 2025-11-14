<?php
// Admin dashboard page
// Include the admin header which enforces admin authentication and prints the opening <main>
include __DIR__ . '/../includes/admin_header.php';
?>
    <h2>Quản trị hệ thống</h2>
    <?php $adminName = htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>
    <div class="admin-card">
        <h3>Chào mừng quản trị viên <?= $adminName ?>!</h3>
        <p>Dưới đây là các số liệu thống kê tổng quan về hệ thống. Bạn có thể chọn các mục trên thanh điều hướng để xem chi tiết.</p>
        <div id="admin-stats" class="admin-stats-grid">
            <div class="stat-card">
                <h4>Tổng sản phẩm</h4>
                <p id="stat-total-products">0</p>
            </div>
            <div class="stat-card">
                <h4>Tổng người dùng</h4>
                <p id="stat-total-users">0</p>
            </div>
            <div class="stat-card">
                <h4>Đơn hàng đã hoàn thành</h4>
                <p id="stat-completed-orders">0</p>
            </div>
            <div class="stat-card">
                <h4>Doanh thu</h4>
                <p id="stat-revenue">0₫</p>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/admin_dashboard.js" defer></script>
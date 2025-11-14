// Script to load admin dashboard statistics
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('/techshop-ai-template/api/get_admin_stats.php');
        const data = await res.json();
        if (data && data.success) {
            const stats = data.stats;
            // Tổng sản phẩm
            const totalProductsEl = document.getElementById('stat-total-products');
            if (totalProductsEl) totalProductsEl.textContent = stats.total_products;
            // Tổng người dùng
            const totalUsersEl = document.getElementById('stat-total-users');
            if (totalUsersEl) totalUsersEl.textContent = stats.total_users;
            // Đơn hàng hoàn thành
            const completedOrders = stats.orders && stats.orders.completed ? stats.orders.completed : 0;
            const completedOrdersEl = document.getElementById('stat-completed-orders');
            if (completedOrdersEl) completedOrdersEl.textContent = completedOrders;
            // Doanh thu
            const revenueEl = document.getElementById('stat-revenue');
            if (revenueEl) revenueEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(stats.revenue || 0);
        }
    } catch (err) {
        console.error('Error loading admin stats', err);
    }
});
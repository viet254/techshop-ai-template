<?php
// Trang quản lý đơn hàng (admin)
// Include admin header (enforces admin authentication and opens <main>)
include __DIR__ . '/../includes/admin_header.php';
?>
    <!-- Nội dung trang quản lý đơn hàng -->
    <h2>Quản lý đơn hàng</h2>
    <div class="admin-card">
    <table class="admin-table" id="orders-table">
        <thead>
            <tr>
                <th>Mã đơn</th><th>Khách hàng</th><th>Thành tiền</th><th>Trạng thái</th><th>Ngày tạo</th><th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <!-- Đơn hàng sẽ được tải bằng JS -->
        </tbody>
    </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadOrders();
});
async function loadOrders() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_admin_orders.php');
        const orders = await res.json();
        const tbody = document.querySelector('#orders-table tbody');
        tbody.innerHTML = '';
        orders.forEach(order => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><a href="/techshop-ai-template/admin/order_detail.php?id=${order.id}">#${order.id}</a></td>
                <td>${order.username || 'Unknown'}</td>
                <td>${Number(order.final_total).toLocaleString()}₫</td>
                <td>
                    <select data-id="${order.id}" onchange="changeStatus(this)">
                        <option value="Pending" ${order.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Processing" ${order.status === 'Processing' ? 'selected' : ''}>Processing</option>
                        <option value="Shipping" ${order.status === 'Shipping' ? 'selected' : ''}>Shipping</option>
                        <option value="Completed" ${order.status === 'Completed' ? 'selected' : ''}>Completed</option>
                        <option value="Cancelled" ${order.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </td>
                <td>${order.created_at}</td>
                <td><button onclick="updateOrder(${order.id}, this.previousElementSibling.value)">Cập nhật</button></td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
    }
}
function changeStatus(select) {
    // no immediate action; update occurs on button click
}
async function updateOrder(orderId, status) {
    try {
        const res = await fetch('/techshop-ai-template/api/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: orderId, status })
        });
        const data = await res.json();
        if (!data.success) alert(data.message || 'Cập nhật thất bại');
    } catch (err) {
        alert('Không thể cập nhật đơn hàng.');
    }
}
</script>
<?php
// Trang chi tiết đơn hàng cho admin
// Include admin header which enforces admin authentication and prints <main>
include __DIR__ . '/../includes/admin_header.php';
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    // Invalid order; show message and stop rendering further content
    echo '<p>Đơn hàng không hợp lệ.</p>';
    // Close the main tag opened in admin_header
    echo '</main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}
?>
    <!-- Nội dung trang chi tiết đơn hàng cho admin -->
    <h2>Chi tiết đơn hàng #<?php echo htmlspecialchars($orderId); ?></h2>
    <div class="admin-card">
        <div id="order-info"></div>
        <table class="admin-table" id="order-items-table">
            <thead>
                <tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th></tr>
            </thead>
            <tbody></tbody>
        </table>
        <p id="order-total"></p>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const orderId = <?php echo $orderId; ?>;
    try {
        const res = await fetch(`/techshop-ai-template/api/get_order_detail.php?id=${orderId}`);
        const data = await res.json();
        if (!data || !data.items) {
            document.getElementById('order-info').innerHTML = '<p>Không tìm thấy đơn hàng.</p>';
            return;
        }
        const info = data.info;
        const items = data.items;
        let html = `<p>Mã đơn: #${info.id}</p>`;
        html += `<p>Người đặt: ${info.username || ''} (${info.email || ''})</p>`;
        html += `<p>Ngày tạo: ${info.created_at}</p>`;
        html += `<p>Trạng thái: ${info.status}</p>`;
        // Thông tin địa chỉ
        if (info.address) {
            html += `<p>Địa chỉ: ${info.address.recipient_name} - ${info.address.address} (${info.address.phone || ''})</p>`;
        }
        // Thông tin voucher
        if (info.voucher_code) {
            html += `<p>Voucher: ${info.voucher_code}</p>`;
            html += `<p>Giảm giá: ${Number(info.discount).toLocaleString()}₫</p>`;
        }
        html += `<p>Tổng đơn: ${Number(info.total).toLocaleString()}₫</p>`;
        html += `<p>Thành tiền: ${Number(info.final_total).toLocaleString()}₫</p>`;
        document.getElementById('order-info').innerHTML = html;
        const tbody = document.querySelector('#order-items-table tbody');
        tbody.innerHTML = '';
        let total = 0;
        items.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            const row = document.createElement('tr');
            row.innerHTML = `<td>${item.name}</td><td>${Number(item.price).toLocaleString()}₫</td><td>${item.quantity}</td><td>${Number(subtotal).toLocaleString()}₫</td>`;
            tbody.appendChild(row);
        });
        document.getElementById('order-total').textContent = '';
    } catch (err) {
        document.getElementById('order-info').innerHTML = '<p>Lỗi khi tải dữ liệu.</p>';
    }
});
</script>
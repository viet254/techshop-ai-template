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
        const res = await fetch(`/api/get_order_detail.php?id=${orderId}`);
        // Nếu server trả lỗi (ví dụ 500) thì hiển thị thông báo lỗi và không tiếp tục
        if (!res.ok) {
            document.getElementById('order-info').innerHTML = '<p>Lỗi khi tải dữ liệu.</p>';
            return;
        }
        let data;
        try {
            data = await res.json();
        } catch (e) {
            document.getElementById('order-info').innerHTML = '<p>Lỗi khi tải dữ liệu.</p>';
            return;
        }
        if (!data || !data.items) {
            document.getElementById('order-info').innerHTML = '<p>Không tìm thấy đơn hàng.</p>';
            return;
        }
        const info = data.info;
        const items = data.items;
        let html = `<p>Mã đơn: #${info.id}</p>`;
        html += `<p>Người đặt: ${info.username || ''} (${info.email || ''})</p>`;
        html += `<p>Ngày tạo: ${info.created_at}</p>`;
        // Hiển thị trạng thái bằng tiếng Việt nếu có mapping
        const statusMap = {
            'Pending': 'Đang chờ',
            'Processing': 'Đang xử lý',
            'Shipping': 'Đang giao',
            'Completed': 'Hoàn thành',
            'Cancelled': 'Đã hủy'
        };
        const viStatus = statusMap[(info.status || '').charAt(0).toUpperCase() + (info.status || '').slice(1)] || statusMap[info.status] || info.status;
        html += `<p>Trạng thái: ${viStatus}</p>`;
        // Thông tin địa chỉ
        if (info.address) {
            html += `<p>Địa chỉ: ${info.address.recipient_name} - ${info.address.address} (${info.address.phone || ''})</p>`;
        }
        // Thông tin voucher
        if (info.voucher_code) {
            html += `<p>Voucher: ${info.voucher_code}</p>`;
            html += `<p>Giảm giá: ${Number(info.discount).toLocaleString()}₫</p>`;
        }
        // Tính phí vận chuyển bằng cách lấy thành tiền trừ tổng cộng và cộng lại giảm giá
        const shippingCost = (Number(info.final_total) - Number(info.total) + Number(info.discount));
        html += `<p>Tổng đơn: ${Number(info.total).toLocaleString()}₫</p>`;
        // Hiển thị voucher và giảm giá nếu có đã được thêm ở trên
        if (shippingCost > 0) {
            html += `<p>Phí vận chuyển: ${Number(shippingCost).toLocaleString()}₫</p>`;
        }
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
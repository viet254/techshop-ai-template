<?php
// Chi tiết đơn hàng cho người dùng
include __DIR__ . '/includes/header.php';
// Kiểm tra đăng nhập, nếu chưa đăng nhập chuyển hướng
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}
// Lấy ID đơn hàng từ query string
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    echo '<main class="main-content"><p>Đơn hàng không tồn tại.</p></main>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>
<main class="main-content">
    <h2>Chi tiết đơn hàng #<?php echo htmlspecialchars($orderId); ?></h2>
    <div id="order-info">
        <!-- Thông tin đơn hàng sẽ được tải bằng JS -->
    </div>
    <table class="cart-table" id="order-items-table">
        <thead>
            <tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th></tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <p id="order-total"></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
// JS để tải chi tiết đơn hàng
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
            // Nếu không thể parse JSON, xem như không có dữ liệu hợp lệ
            document.getElementById('order-info').innerHTML = '<p>Lỗi khi tải dữ liệu.</p>';
            return;
        }
        if (!data || !data.items) {
            document.getElementById('order-info').innerHTML = '<p>Không tìm thấy đơn hàng.</p>';
            return;
        }
        const info = data.info;
        const items = data.items;
        // Hiển thị thông tin đơn hàng
        // Mapping trạng thái tiếng Anh sang tiếng Việt
        const statusMap = {
            'Pending': 'Đang chờ',
            'Processing': 'Đang xử lý',
            'Shipping': 'Đang vận chuyển',
            'Completed': 'Thành công',
            'Cancelled': 'Đã hủy'
        };
        const viStatus = statusMap[(info.status || '').charAt(0).toUpperCase() + (info.status || '').slice(1)] || statusMap[info.status] || info.status;
        // Khởi tạo HTML hiển thị thông tin đơn hàng
        let infoHtml = `
            <p>Mã đơn: #${info.id}</p>
            <p>Ngày tạo: ${info.created_at}</p>
            <p>Trạng thái: ${viStatus}</p>
        `;
        // Hiển thị địa chỉ
        if (info.address) {
            infoHtml += `<p>Địa chỉ: ${info.address.recipient_name} - ${info.address.address} (${info.address.phone || ''})</p>`;
        }
        // Hiển thị voucher và giảm giá nếu có
        if (info.voucher_code) {
            infoHtml += `<p>Voucher: ${info.voucher_code}</p>`;
            infoHtml += `<p>Giảm giá: ${Number(info.discount).toLocaleString()}₫</p>`;
        }
        // Tính phí vận chuyển bằng cách lấy thành tiền trừ tổng đơn cộng lại giảm giá
        const shippingCost = (Number(info.final_total) - Number(info.total) + Number(info.discount));
        infoHtml += `<p>Tổng đơn: ${Number(info.total).toLocaleString()}₫</p>`;
        // Chỉ hiển thị phí vận chuyển khi có giá trị dương
        if (shippingCost > 0) {
            infoHtml += `<p>Phí vận chuyển: ${Number(shippingCost).toLocaleString()}₫</p>`;
        }
        infoHtml += `<p>Thành tiền: ${Number(info.final_total).toLocaleString()}₫</p>`;
        // Nếu có lý do hủy, hiển thị
        if (info.status === 'Cancelled' && info.cancel_reason) {
            infoHtml += `<p>Lý do hủy: ${info.cancel_reason}</p>`;
        }
        document.getElementById('order-info').innerHTML = infoHtml;
        // Hiển thị danh sách sản phẩm
        const tbody = document.querySelector('#order-items-table tbody');
        tbody.innerHTML = '';
        let total = 0;
        items.forEach(item => {
            const row = document.createElement('tr');
            const subtotal = item.price * item.quantity;
            total += subtotal;
            row.innerHTML = `<td>${item.name}</td><td>${Number(item.price).toLocaleString()}₫</td><td>${item.quantity}</td><td>${Number(subtotal).toLocaleString()}₫</td>`;
            tbody.appendChild(row);
        });
        // Không cần hiển thị tổng ở đây vì đã hiển thị trong info
        document.getElementById('order-total').textContent = '';
    } catch (err) {
        document.getElementById('order-info').innerHTML = '<p>Lỗi khi tải dữ liệu.</p>';
        console.error(err);
    }
});
</script>
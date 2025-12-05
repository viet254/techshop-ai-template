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
        const res = await fetch('/api/get_admin_orders.php');
        const orders = await res.json();
        const tbody = document.querySelector('#orders-table tbody');
        tbody.innerHTML = '';
        // Danh sách trạng thái với nhãn tiếng Việt. Giá trị giữ nguyên để gửi cho API.
        const statusList = [
            { value: 'Pending', label: 'Đang chờ' },
            { value: 'Processing', label: 'Đang xử lý' },
            { value: 'Shipping', label: 'Đang giao' },
            { value: 'Completed', label: 'Hoàn thành' },
            { value: 'Cancelled', label: 'Đã hủy' }
        ];
        orders.forEach(order => {
            const tr = document.createElement('tr');
            // Tạo các option cho select với nhãn tiếng Việt và đánh dấu chọn phù hợp
            const optionsHtml = statusList.map(st => {
                const selected = (order.status || '').toLowerCase() === st.value.toLowerCase() ? 'selected' : '';
                return `<option value="${st.value}" ${selected}>${st.label}</option>`;
            }).join('');
            tr.innerHTML = `
                <td><a href="/admin/order_detail.php?id=${order.id}">#${order.id}</a></td>
                <td>${order.username || 'Unknown'}</td>
                <td>${Number(order.final_total).toLocaleString()}₫</td>
                <td>
                    <select data-id="${order.id}" onchange="changeStatus(this)">
                        ${optionsHtml}
                    </select>
                </td>
                <td>${order.created_at}</td>
                <td>
                    <button class="btn-edit" onclick="updateOrder(${order.id}, this.parentElement.parentElement.querySelector('select').value)"><span class="icon">🔄</span> Cập nhật</button>
                    <button class="btn-delete" onclick="deleteOrder(${order.id}, this)"><span class="icon">🗑️</span> Xóa</button>
                </td>
            `;
            // Cho phép click vào hàng để xem chi tiết đơn hàng (trừ khi bấm vào nút hoặc chọn trạng thái)
            tr.addEventListener('click', function(e) {
                const tag = e.target.tagName.toLowerCase();
                if (tag === 'button' || tag === 'select' || tag === 'option' || (e.target.closest('button') !== null) || (e.target.closest('select') !== null)) {
                    return;
                }
                window.location.href = `/admin/order_detail.php?id=${order.id}`;
            });
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
        const res = await fetch('/api/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: orderId, status })
        });
        const data = await res.json();
        if (!data.success) {
            showNotification(data.message || 'Cập nhật thất bại', 'error');
        }
    } catch (err) {
        showNotification('Không thể cập nhật đơn hàng.', 'error');
    }
}

// Hàm xóa đơn hàng
async function deleteOrder(orderId, btn) {
    if (!confirm('Bạn có chắc chắn muốn xóa đơn hàng #' + orderId + ' không?')) {
        return;
    }
    try {
        const res = await fetch('/api/delete_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: orderId })
        });
        const data = await res.json();
        if (data && data.success) {
            // Xóa hàng khỏi bảng
            const row = btn.closest('tr');
            if (row) row.remove();
            showNotification(data.message || 'Đã xóa đơn hàng.', 'success');
        } else {
            showNotification(data.message || 'Không thể xóa đơn hàng.', 'error');
        }
    } catch (err) {
        showNotification('Không thể xóa đơn hàng.', 'error');
    }
}
</script>
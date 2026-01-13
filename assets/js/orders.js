// JS cho trang Đơn hàng của tôi
document.addEventListener('DOMContentLoaded', async () => {
    const ordersNavLinks = document.querySelectorAll('.orders-nav a');
    let orders = [];
    // Lấy các phần tử modal và các control bên trong
    const cancelModal = document.getElementById('cancel-modal');
    const cancelOrderIdDisplay = document.getElementById('cancel-order-id-display');
    const cancelReasonRadios = () => document.querySelectorAll('.cancel-options input[name="cancel-reason"]');
    const cancelOtherInput = document.getElementById('cancel-other-input');
    const cancelConfirmBtn = document.getElementById('cancel-confirm-btn');
    const cancelCancelBtn = document.getElementById('cancel-cancel-btn');
    let currentCancelOrderId = null;

    // Tải danh sách đơn hàng từ server
    try {
        const res = await fetch('/api/get_order_history.php', { credentials: 'same-origin' });
        orders = await res.json();
    } catch (err) {
        console.error(err);
    }
    // Chuyển trạng thái ngắn thành tiếng Việt
    function getStatusText(status) {
        switch(status) {
            case 'Pending': return 'Mới đặt';
            case 'Processing': return 'Đang xử lý';
            case 'Shipping': return 'Đang vận chuyển';
            case 'Completed': return 'Thành công';
            case 'Cancelled': return 'Đã hủy';
            default: return status;
        }
    }
    // Hàm hiển thị đơn hàng theo trạng thái
    function renderOrders(status) {
        const container = document.getElementById('orders-list');
        container.innerHTML = '';
        // Lọc đơn hàng theo trạng thái
        let filtered = orders;
        if (status && status !== 'all' && status !== 'All') {
            filtered = orders.filter(o => o.status === status);
        }
        if (!filtered || filtered.length === 0) {
            container.innerHTML = '<p>Không có đơn hàng.</p>';
            return;
        }
        filtered.forEach(order => {
            const firstName = order.first_product_name || 'Đơn hàng';
            const itemsCount = order.items_count ? Number(order.items_count) : null;
            const moreItemsText = itemsCount && itemsCount > 1 ? ` +${itemsCount - 1} sản phẩm khác` : '';
            const imageHtml = order.first_product_image 
                ? `<div class="order-thumb"><img src="assets/images/${order.first_product_image}" alt="${firstName}"></div>`
                : '';
            const div = document.createElement('div');
            div.className = 'order-card';
            const statusText = getStatusText(order.status);
            // Nếu trạng thái chưa hoàn thành và chưa hủy thì tạo nút hủy
            let cancelBtnHtml = '';
            if (order.status !== 'Completed' && order.status !== 'Cancelled') {
                cancelBtnHtml = `<button class="cancel-order" data-id="${order.id}">Hủy đơn</button>`;
            }
            div.innerHTML = `
                ${imageHtml}
                <div class="order-main">
                    <p class="order-title">${firstName}${moreItemsText}</p>
                    <p class="order-meta">Mã đơn: <a href="/order_detail.php?id=${order.id}">#${order.id}</a> · ${order.created_at}</p>
                    <p class="order-status-text">Trạng thái: ${statusText}</p>
                    <p class="order-total">Tổng: ${Number(order.final_total).toLocaleString()}₫</p>
                    ${cancelBtnHtml}
                </div>
            `;
            // Khi click vào thẻ đơn hàng, điều hướng tới trang chi tiết (trừ khi nhấn nút Hủy)
            div.addEventListener('click', function(e) {
                // Nếu click vào nút hủy đơn thì không chuyển trang
                if (e.target && (e.target.classList && e.target.classList.contains('cancel-order'))) return;
                // Nếu click vào liên kết cụ thể thì để liên kết hoạt động
                if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'a') return;
                window.location.href = `/order_detail.php?id=${order.id}`;
            });
            container.appendChild(div);
        });
        // Gắn sự kiện cho các nút hủy đơn
        container.querySelectorAll('.cancel-order').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.getAttribute('data-id');
                currentCancelOrderId = parseInt(orderId);
                // Hiển thị modal và điền ID
                cancelOrderIdDisplay.textContent = orderId;
                // Reset radio và textarea
                cancelReasonRadios().forEach(r => r.checked = false);
                cancelOtherInput.value = '';
                cancelOtherInput.style.display = 'none';
                cancelModal.classList.remove('hidden');
            });
        });
    }
    // Lắng nghe sự thay đổi trên radio (dùng delegation vì các radio có thể chưa tồn tại khi trang load)
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('.cancel-options input[name="cancel-reason"]')) {
            if (e.target.value === 'Khác') {
                cancelOtherInput.style.display = 'block';
            } else {
                cancelOtherInput.style.display = 'none';
                cancelOtherInput.value = '';
            }
        }
    });
    // Nút xác nhận hủy
    cancelConfirmBtn.addEventListener('click', async () => {
        if (!currentCancelOrderId) return;
        // Lấy radio được chọn
        const selected = [...cancelReasonRadios()].find(r => r.checked);
        if (!selected) {
            // Notify error if no reason selected
            showNotification('Vui lòng chọn lý do hủy!', 'error');
            return;
        }
        let reason = selected.value;
        if (reason === 'Khác') {
            reason = cancelOtherInput.value.trim();
            if (!reason) {
                showNotification('Vui lòng nhập lý do khác!', 'error');
                return;
            }
        }
        try {
            const res = await fetch('/api/cancel_order.php', { credentials: 'same-origin',
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: currentCancelOrderId, reason })
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Đơn hàng đã được hủy.', 'success');
                // Cập nhật trạng thái trong mảng orders
                orders = orders.map(o => o.id === currentCancelOrderId ? { ...o, status: 'Cancelled' } : o);
                // Ẩn modal
                cancelModal.classList.add('hidden');
                currentCancelOrderId = null;
                // Re-render theo tab hiện tại
                const activeStatus = document.querySelector('.orders-nav .active').getAttribute('data-status');
                renderOrders(activeStatus);
            } else {
                showNotification(data.error || 'Không thể hủy đơn hàng', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Đã xảy ra lỗi. Vui lòng thử lại sau.', 'error');
        }
    });
    // Nút đóng modal
    cancelCancelBtn.addEventListener('click', () => {
        cancelModal.classList.add('hidden');
        currentCancelOrderId = null;
    });
    // Gắn sự kiện click cho các tab
    ordersNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            ordersNavLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            const status = this.getAttribute('data-status');
            renderOrders(status);
        });
    });
    // Hiển thị tất cả mặc định
    renderOrders('all');
});
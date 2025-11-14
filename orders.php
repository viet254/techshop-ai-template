<?php
// Trang đơn hàng của người dùng
include __DIR__ . '/includes/header.php';
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: /techshop-ai-template/login.php');
    exit;
}
?>
<main class="main-content orders-page">
    <h2>Đơn hàng của tôi</h2>
    <nav class="orders-nav">
        <a href="#" data-status="all" class="active">Tất cả</a>
        <a href="#" data-status="Pending">Mới đặt</a>
        <a href="#" data-status="Processing">Đang xử lý</a>
        <a href="#" data-status="Shipping">Đang vận chuyển</a>
        <a href="#" data-status="Completed">Thành công</a>
        <a href="#" data-status="Cancelled">Đã hủy</a>
    </nav>
    <div id="orders-list" class="orders-list"></div>

    <!-- Modal hủy đơn hàng -->
    <div id="cancel-modal" class="modal hidden">
        <!-- Lớp phủ làm mờ nền -->
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <h3>Hủy đơn hàng #<span id="cancel-order-id-display"></span></h3>
            <!-- Các lựa chọn lý do hủy dưới dạng radio -->
            <div class="cancel-options" style="display:flex; flex-direction:column; gap:5px; margin-top:10px;">
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="radio" name="cancel-reason" value="Thay đổi ý" /> Thay đổi ý
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="radio" name="cancel-reason" value="Đặt nhầm" /> Đặt nhầm
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="radio" name="cancel-reason" value="Muốn đổi sản phẩm khác" /> Muốn đổi sản phẩm khác
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="radio" name="cancel-reason" value="Khác" /> Lý do khác
                </label>
            </div>
            <!-- Ô nhập nếu chọn lý do khác -->
            <textarea id="cancel-other-input" placeholder="Nhập lý do khác..." style="display:none;margin-top:10px; padding:8px; border:1px solid #ccc; border-radius:5px;"></textarea>
            <!-- Nút xác nhận và đóng -->
            <div class="modal-buttons" style="margin-top:15px; display:flex; justify-content:flex-end; gap:10px;">
                <button id="cancel-confirm-btn" style="padding:8px 12px; background:#e53935; color:#fff; border:none; border-radius:5px; cursor:pointer;">Xác nhận</button>
                <button id="cancel-cancel-btn" style="padding:8px 12px; background:#ccc; color:#333; border:none; border-radius:5px; cursor:pointer;">Đóng</button>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/orders.js" defer></script>
<?php
// Trang giỏ hàng
include __DIR__ . '/includes/header.php';
// Yêu cầu đăng nhập để truy cập giỏ hàng
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}
?>
<main class="main-content">
    <h2>Giỏ hàng của bạn</h2>
    <div class="cart-container">
        <h3>Sản phẩm sẽ thanh toán</h3>
        <div class="checkout-options" id="checkout-options" style="margin-bottom:20px;">
            <!-- Địa chỉ giao hàng, phương thức thanh toán và voucher sẽ được load bằng JS -->
            <div id="address-select-container" class="checkout-section" style="margin-bottom:10px;"></div>
            <div id="payment-select-container" class="checkout-section" style="margin-bottom:10px;"></div>
            <div id="voucher-container" class="checkout-section" style="margin-bottom:10px;"></div>
            <div id="discount-info" style="font-style: italic; color: #e53935;"></div>
        </div>
        <table class="cart-table" id="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="cart-items">
                <!-- Items dynamic -->
            </tbody>
        </table>
        <div class="cart-total">
            <strong>Tổng cộng:</strong> <span id="cart-total">0₫</span><br/>
            <span id="final-total-area" style="display:none;"><strong>Thành tiền:</strong> <span id="final-total">0₫</span></span>
        </div>
        <button id="checkout-btn">Tiến hành thanh toán</button>
    </div>
    <div class="saved-container">
        <h3>Sản phẩm yêu thích</h3>
        <table class="cart-table" id="saved-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="saved-items">
                <!-- Saved items dynamic -->
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/cart.js" defer></script>
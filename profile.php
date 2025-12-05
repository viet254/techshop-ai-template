<?php
// Trang cá nhân người dùng
include __DIR__ . '/includes/header.php';
// Kiểm tra đăng nhập; nếu chưa đăng nhập, chuyển tới trang đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}
?>
<main class="main-content profile-page">
    <h2>Trang cá nhân</h2>
    <div class="profile-container">
        <!-- Thanh điều hướng bên trái cho trang cá nhân -->
        <aside class="profile-nav">
            <ul>
                <li><a href="#" data-section="account" class="active">Tài khoản của tôi</a></li>
                <li><a href="/orders.php">Đơn mua</a></li>
                <li><a href="#" data-section="vouchers">Kho voucher</a></li>
                <li><a href="#" data-section="addresses">Sổ địa chỉ</a></li>
                <li><a href="/api/logout.php">Đăng xuất</a></li>
            </ul>
        </aside>
        <!-- Khu vực nội dung -->
        <section class="profile-content">
            <!-- Thông tin tài khoản -->
            <section id="account-section" class="profile-section">
                <h3>Thông tin cá nhân</h3>
                <div class="avatar-upload">
                    <img id="avatar-preview" src="" alt="Avatar" />
                    <!-- Nút chọn ảnh và tải lên. Input file ẩn sẽ được kích hoạt khi nhấn nút -->
                    <input type="file" id="avatar-input" accept="image/*" style="display:none" />
                    <button id="change-avatar-btn">Thay ảnh</button>
                </div>
                <form id="profile-form">
                    <label>Họ và tên:
                        <input type="text" id="profile-name" name="name" />
                    </label>
                    <label>Email:
                        <input type="email" id="profile-email" name="email" />
                    </label>
                    <label>Điện thoại:
                        <input type="text" id="profile-phone" name="phone" />
                    </label>
                    <button type="submit">Cập nhật</button>
                </form>
                <div class="change-password">
                    <h3>Đổi mật khẩu</h3>
                    <form id="password-form">
                        <label>Mật khẩu cũ:
                            <input type="password" id="old-password" />
                        </label>
                        <label>Mật khẩu mới:
                            <input type="password" id="new-password" />
                        </label>
                        <label>Xác nhận mật khẩu mới:
                            <input type="password" id="confirm-password" />
                        </label>
                        <button type="submit">Đổi mật khẩu</button>
                    </form>
                </div>
            </section>
            <!-- Kho voucher -->
            <section id="vouchers-section" class="profile-section hidden">
                <h3>Kho voucher</h3>
                <div id="voucher-list">Đang tải...</div>
            </section>
            <!-- Sổ địa chỉ -->
            <section id="addresses-section" class="profile-section hidden">
                <h3>Địa chỉ giao hàng của quý khách</h3>
                <!-- Shipping address form styled to match provided design -->
                <form id="address-form" class="shipping-form">
                    <div class="form-group">
                        <label>Khách hàng: *</label>
                        <input type="text" id="addr-recipient" required />
                    </div>
                    <div class="form-group">
                        <label>Email: *</label>
                        <input type="email" id="addr-email" required />
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="text" id="addr-phone" />
                    </div>
                    <div class="form-group">
                        <label>Tỉnh/Thành phố: *</label>
                    <select id="addr-city" required>
                        <option value="">--- Chọn tỉnh/thành ---</option>
                        <option value="Ha Noi">Thành phố Hà Nội</option>
                        <option value="Bac Ninh">Tỉnh Bắc Ninh</option>
                        <option value="Hai Phong">Thành phố Hải Phòng</option>
                        <option value="Quang Ninh">Tỉnh Quảng Ninh</option>
                        <option value="Bac Giang">Tỉnh Bắc Giang</option>
                        <option value="Thai Nguyen">Tỉnh Thái Nguyên</option>
                        <option value="Vinh Phuc">Tỉnh Vĩnh Phúc</option>
                        <option value="Nam Dinh">Tỉnh Nam Định</option>
                        <option value="Ninh Binh">Tỉnh Ninh Bình</option>
                        <option value="Ha Nam">Tỉnh Hà Nam</option>
                        <option value="Hai Duong">Tỉnh Hải Dương</option>
                    </select>
                    </div>
                    <div class="form-group">
                        <label>Quận/Huyện:</label>
                        <select id="addr-district">
                            <option value="">--- Chọn quận/huyện ---</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ giao hàng:</label>
                        <textarea id="addr-address" required placeholder="Địa chỉ giao hàng"></textarea>
                    </div>
                    <button type="submit" id="address-submit-btn">Thêm địa chỉ nhận</button>
                </form>
                <div id="address-list"></div>
            </section>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<!-- Script riêng cho trang cá nhân -->
<script src="assets/js/profile.js" defer></script>
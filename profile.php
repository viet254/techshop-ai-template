<?php
// Trang cá nhân người dùng
include __DIR__ . '/includes/header.php';
// Kiểm tra đăng nhập; nếu chưa đăng nhập, chuyển tới trang đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: /techshop-ai-template/login.php');
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
                <li><a href="/techshop-ai-template/orders.php">Đơn mua</a></li>
                <li><a href="#" data-section="vouchers">Kho voucher</a></li>
                <li><a href="#" data-section="addresses">Sổ địa chỉ</a></li>
                <li><a href="/techshop-ai-template/api/logout.php">Đăng xuất</a></li>
            </ul>
        </aside>
        <!-- Khu vực nội dung -->
        <section class="profile-content">
            <!-- Thông tin tài khoản -->
            <section id="account-section" class="profile-section">
                <h3>Thông tin cá nhân</h3>
                <div class="avatar-upload">
                    <img id="avatar-preview" src="" alt="Avatar" />
                    <input type="file" id="avatar-input" accept="image/*" />
                    <button id="upload-avatar-btn">Tải ảnh</button>
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
                <h3>Sổ địa chỉ</h3>
                <form id="address-form">
                    <label>Tên người nhận:
                        <input type="text" id="addr-recipient" required />
                    </label>
                    <label>Số điện thoại:
                        <input type="text" id="addr-phone" />
                    </label>
                    <label>Địa chỉ:
                        <input type="text" id="addr-address" required />
                    </label>
                    <button type="submit">Thêm địa chỉ</button>
                </form>
                <div id="address-list"></div>
            </section>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<!-- Script riêng cho trang cá nhân -->
<script src="/techshop-ai-template/assets/js/profile.js" defer></script>
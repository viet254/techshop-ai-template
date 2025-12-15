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
    <section class="profile-hero">
        <div class="hero-main">
            <div class="hero-avatar">
                <img id="avatar-preview" src="" alt="Avatar" />
                <input type="file" id="avatar-input" accept="image/*" style="display:none" />
                <button id="change-avatar-btn">Thay ảnh</button>
            </div>
            <div class="hero-info">
                <p class="eyebrow">Xin chào,</p>
                <h2 id="profile-name-display">Trang cá nhân</h2>
                <p class="muted" id="profile-email-display"></p>
                <div class="hero-tags">
                    <span class="tag" id="profile-phone-display">Chưa cập nhật số điện thoại</span>
                    <span class="tag subtle">Thành viên</span>
                </div>
                <div class="hero-actions">
                    <a class="ghost-btn" href="/orders.php">Đơn mua</a>
                    <a class="ghost-btn profile-shortcut" href="#" data-section="vouchers">Kho voucher</a>
                    <a class="ghost-btn profile-shortcut" href="#" data-section="addresses">Sổ địa chỉ</a>
                </div>
            </div>
        </div>
        <div class="hero-stats">
            <div class="stat-card">
                <p>Đơn mua</p>
                <strong id="stat-order-count">--</strong>
                <small>Đang theo dõi</small>
            </div>
            <div class="stat-card">
                <p>Voucher</p>
                <strong id="stat-voucher-count">--</strong>
                <small>Voucher khả dụng</small>
            </div>
            <div class="stat-card">
                <p>Địa chỉ</p>
                <strong id="stat-address-count">--</strong>
                <small>Sổ địa chỉ đã lưu</small>
            </div>
        </div>
    </section>

    <div class="profile-container profile-layout">
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
            <section id="account-section" class="profile-section profile-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Tài khoản</p>
                        <h3>Thông tin cá nhân</h3>
                    </div>
                    <span class="status-pill success">Hoạt động</span>
                </div>
                <form id="profile-form" class="form-grid">
                    <label class="field">
                        <span>Họ và tên</span>
                        <input type="text" id="profile-name" name="name" />
                    </label>
                    <label class="field">
                        <span>Email</span>
                        <input type="email" id="profile-email" name="email" />
                    </label>
                    <label class="field">
                        <span>Điện thoại</span>
                        <input type="text" id="profile-phone" name="phone" />
                    </label>
                    <div class="form-actions">
                        <button type="submit">Cập nhật thông tin</button>
                    </div>
                </form>
            </section>
            <section class="profile-section profile-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Bảo mật</p>
                        <h3>Đổi mật khẩu</h3>
                    </div>
                </div>
                <form id="password-form" class="form-grid three-cols">
                    <label class="field">
                        <span>Mật khẩu cũ</span>
                        <input type="password" id="old-password" />
                    </label>
                    <label class="field">
                        <span>Mật khẩu mới</span>
                        <input type="password" id="new-password" />
                    </label>
                    <label class="field">
                        <span>Xác nhận mật khẩu mới</span>
                        <input type="password" id="confirm-password" />
                    </label>
                    <div class="form-actions">
                        <button type="submit">Đổi mật khẩu</button>
                    </div>
                </form>
            </section>
            <!-- Kho voucher -->
            <section id="vouchers-section" class="profile-section profile-card hidden">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Ưu đãi</p>
                        <h3>Kho voucher</h3>
                    </div>
                </div>
                <div id="voucher-list">Đang tải...</div>
            </section>
            <!-- Sổ địa chỉ -->
            <section id="addresses-section" class="profile-section profile-card hidden">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Giao hàng</p>
                        <h3>Sổ địa chỉ</h3>
                    </div>
                </div>
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
                <div id="address-list" class="address-list"></div>
            </section>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<!-- Script riêng cho trang cá nhân -->
<script src="assets/js/profile.js" defer></script>
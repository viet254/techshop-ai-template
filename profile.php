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
    <div class="profile-container profile-layout">
        <!-- Thanh điều hướng bên trái cho trang cá nhân -->
        <aside class="profile-nav">
            <div class="profile-sidebar-header">
                <div class="profile-sidebar-avatar">
                    <img id="sidebar-avatar" src="/assets/images/default-avatar.png" alt="Avatar">
                </div>
                <div class="profile-sidebar-info">
                    <div class="profile-sidebar-name" id="sidebar-name-display">Người dùng</div>
                    <a href="#" class="profile-edit-link" data-section="account">Sửa Hồ Sơ</a>
                </div>
            </div>
            <ul class="profile-menu">
                <li><a href="#" data-section="notifications">Thông Báo</a></li>
                <li><a href="#" data-section="account" class="active">Tài Khoản Của Tôi</a></li>
                <li><a href="#" data-section="orders">Đơn Mua</a></li>
                <li><a href="#" data-section="vouchers">Kho Voucher</a></li>
                <li><a href="#" data-section="addresses">Sổ Địa Chỉ</a></li>
                <li><a href="/api/logout.php">Đăng Xuất</a></li>
            </ul>
        </aside>
        <!-- Khu vực nội dung -->
        <section class="profile-content">
            <!-- Thông báo (đơn giản, placeholder) -->
            <section id="notifications-section" class="profile-section profile-card hidden">
                <h3 class="profile-section-title">Thông báo</h3>
                <p>Hiện chưa có thông báo mới.</p>
            </section>
            <!-- Thông tin tài khoản -->
            <section id="account-section" class="profile-section profile-card">
                <div class="account-profile-header">
                    <h3>Hồ Sơ Của Tôi</h3>
                    <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                </div>
                <div class="account-body">
                    <form id="profile-form" class="account-form">
                        <div class="account-row">
                            <div class="account-label">Tên đăng nhập</div>
                            <div class="account-field">
                                <span id="profile-username-display"><?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?></span>
                            </div>
                        </div>
                        <div class="account-row">
                            <div class="account-label">Tên</div>
                            <div class="account-field">
                        <input type="text" id="profile-name" name="name" />
                    </div>
                        </div>
                        <div class="account-row">
                            <div class="account-label">Email</div>
                            <div class="account-field">
                        <input type="email" id="profile-email" name="email" />
                    </div>
                        </div>
                        <div class="account-row">
                            <div class="account-label">Số điện thoại</div>
                            <div class="account-field">
                        <input type="text" id="profile-phone" name="phone" />
                    </div>
                        </div>
                        <div class="account-row">
                            <div class="account-label">Giới tính</div>
                            <div class="account-field">
                        <div class="gender-group">
                            <label><input type="radio" name="gender" value="male"> Nam</label>
                            <label><input type="radio" name="gender" value="female"> Nữ</label>
                            <label><input type="radio" name="gender" value="other"> Khác</label>
                        </div>
                    </div>
                        </div>
                        <div class="account-row">
                            <div class="account-label">Ngày sinh</div>
                            <div class="account-field">
                        <input type="date" id="profile-birthday" name="birthday" />
                    </div>
                        </div>
                        <div class="account-actions">
                            <button type="submit" class="btn-save-profile">Lưu</button>
                        </div>
                    </form>
                    <div class="account-avatar-panel">
                        <div class="account-avatar-circle">
                            <img id="avatar-preview" src="/assets/images/default-avatar.png" alt="Avatar">
                        </div>
                        <button id="change-avatar-btn" class="btn-choose-avatar">Chọn Ảnh</button>
                        <p class="account-avatar-note">
                            Dung lượng file tối đa 1 MB<br>
                            Định dạng: JPEG, .PNG
                        </p>
                        <input type="file" id="avatar-input" accept="image/*" style="display:none">
                    </div>
                </div>
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
            <!-- Đơn mua ngay trong trang cá nhân -->
            <section id="orders-section" class="profile-section profile-card hidden">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Đơn hàng</p>
                        <h3>Đơn mua của tôi</h3>
                    </div>
                </div>
                <nav class="orders-nav">
                    <a href="#" data-status="all" class="active">Tất cả</a>
                    <a href="#" data-status="Pending">Chờ xác nhận</a>
                    <a href="#" data-status="Processing">Đang xử lý</a>
                    <a href="#" data-status="Shipping">Đang vận chuyển</a>
                    <a href="#" data-status="Completed">Hoàn thành</a>
                    <a href="#" data-status="Cancelled">Đã hủy</a>
                </nav>
                <div id="orders-list" class="orders-list"></div>
                <!-- Modal hủy đơn hàng (dùng chung với trang Đơn hàng) -->
                <div id="cancel-modal" class="modal hidden">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <h3>Hủy đơn hàng #<span id="cancel-order-id-display"></span></h3>
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
                        <textarea id="cancel-other-input" placeholder="Nhập lý do khác..." style="display:none;margin-top:10px; padding:8px; border:1px solid #ccc; border-radius:5px;"></textarea>
                        <div class="modal-buttons" style="margin-top:15px; display:flex; justify-content:flex-end; gap:10px;">
                            <button id="cancel-confirm-btn" style="padding:8px 12px; background:#e53935; color:#fff; border:none; border-radius:5px; cursor:pointer;">Xác nhận</button>
                            <button id="cancel-cancel-btn" style="padding:8px 12px; background:#ccc; color:#333; border:none; border-radius:5px; cursor:pointer;">Đóng</button>
                        </div>
                    </div>
                </div>
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
                    <button id="open-address-modal" class="btn-add-address">
                        + Thêm địa chỉ mới
                    </button>
                </div>
                <div id="address-list" class="address-list"></div>
                <!-- Modal thêm / sửa địa chỉ -->
                <div id="address-modal-overlay" class="address-modal-overlay hidden">
                    <div class="address-modal">
                        <div class="address-modal-header">
                            <h4 id="address-modal-title">Thêm địa chỉ mới</h4>
                            <button type="button" id="close-address-modal" class="address-modal-close">×</button>
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
                            <div class="address-modal-actions">
                                <button type="button" id="cancel-address-modal" class="btn-cancel-address">Hủy</button>
                                <button type="submit" id="address-submit-btn" class="btn-save-address">Lưu</button>
                            </div>
                </form>
                    </div>
                </div>
            </section>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<!-- Script riêng cho trang cá nhân -->
<script src="assets/js/orders.js" defer></script>
<script src="assets/js/profile.js" defer></script>
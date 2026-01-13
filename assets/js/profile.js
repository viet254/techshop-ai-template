// JS for profile page
document.addEventListener('DOMContentLoaded', async () => {
    await loadProfile();
    bindProfileNav();
    bindProfileForms();
    setupAddressModal();
    loadAddresses();

    const citySelect = document.getElementById('addr-city');
    const districtSelect = document.getElementById('addr-district');
    const districtsByProvince = {
        "Ha Noi": [
            "Ba Đình", "Hoàn Kiếm", "Hai Bà Trưng", "Đống Đa", "Tây Hồ", "Cầu Giấy", "Thanh Xuân", "Long Biên", "Nam Từ Liêm", "Bắc Từ Liêm", "Hà Đông"
        ],
        "Bac Ninh": [
            "TP Bắc Ninh", "Từ Sơn", "Yên Phong", "Tiên Du", "Thuận Thành", "Gia Bình", "Quế Võ", "Lương Tài"
        ],
        "Hai Phong": [
            "Hồng Bàng", "Ngô Quyền", "Lê Chân", "Kiến An", "Hải An", "Đồ Sơn", "An Dương", "An Lão", "Kiến Thụy"
        ],
        "Quang Ninh": [
            "Hạ Long", "Cẩm Phả", "Uông Bí", "Móng Cái", "Quảng Yên", "Đông Triều", "Vân Đồn", "Tiên Yên"
        ],
        "Bac Giang": [
            "TP Bắc Giang", "Yên Thế", "Tân Yên", "Lục Nam", "Lục Ngạn", "Sơn Động", "Yên Dũng", "Việt Yên", "Lạng Giang", "Hiệp Hòa"
        ],
        "Thai Nguyen": [
            "TP Thái Nguyên", "Sông Công", "Định Hóa", "Phú Lương", "Đồng Hỷ", "Võ Nhai", "Phú Bình"
        ],
        "Vinh Phuc": [
            "TP Vĩnh Yên", "Phúc Yên", "Yên Lạc", "Vĩnh Tường", "Tam Dương", "Tam Đảo", "Sông Lô", "Bình Xuyên"
        ],
        "Nam Dinh": [
            "TP Nam Định", "Mỹ Lộc", "Vụ Bản", "Ý Yên", "Trực Ninh", "Xuân Trường", "Giao Thủy", "Nghĩa Hưng", "Nam Trực", "Hải Hậu"
        ],
        "Ninh Binh": [
            "TP Ninh Bình", "Tam Điệp", "Gia Viễn", "Hoa Lư", "Yên Khánh", "Yên Mô", "Kim Sơn", "Nho Quan"
        ],
        "Ha Nam": [
            "TP Phủ Lý", "Duy Tiên", "Lý Nhân", "Kim Bảng", "Thanh Liêm", "Bình Lục"
        ],
        "Hai Duong": [
            "TP Hải Dương", "Chí Linh", "Nam Sách", "Kinh Môn", "Thanh Hà", "Cẩm Giàng", "Gia Lộc", "Tứ Kỳ", "Ninh Giang", "Thanh Miện"
        ]
    };
    if (citySelect && districtSelect) {
        citySelect.addEventListener('change', function() {
            const val = citySelect.value;
            districtSelect.innerHTML = '<option value="">--- Chọn quận/huyện ---</option>';
            if (districtsByProvince[val]) {
                districtsByProvince[val].forEach(function(d) {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    districtSelect.appendChild(opt);
                });
            }
        });
    }
    loadVouchers();
    updateOrderStat();
    bindChangeAvatar();
});

async function loadProfile() {
    try {
        const res = await fetch('/api/get_profile.php');
        const user = await res.json();
        if (user) {
            document.getElementById('profile-name').value = user.name || '';
            document.getElementById('profile-email').value = user.email || '';
            document.getElementById('profile-phone').value = user.phone || '';
            const nameDisplay = document.getElementById('profile-name-display');
            const emailDisplay = document.getElementById('profile-email-display');
            const phoneDisplay = document.getElementById('profile-phone-display');
            const sidebarName = document.getElementById('sidebar-name-display');
            const avatarPreview = document.getElementById('avatar-preview');
            const sidebarAvatar = document.getElementById('sidebar-avatar');

            const displayName = user.name || 'Người dùng';
            const displayEmail = user.email || 'Chưa có email';
            const displayPhone = user.phone || 'Chưa cập nhật số điện thoại';

            if (nameDisplay) nameDisplay.textContent = displayName;
            if (emailDisplay) emailDisplay.textContent = displayEmail;
            if (phoneDisplay) phoneDisplay.textContent = displayPhone;
            if (sidebarName) sidebarName.textContent = displayName;

            const avatarSrc = user.avatar
                ? '/assets/images/avatars/' + user.avatar
                : '/assets/images/default-avatar.png';
            if (avatarPreview) avatarPreview.src = avatarSrc;
            if (sidebarAvatar) sidebarAvatar.src = avatarSrc;
        }
    } catch (err) {
        console.error(err);
    }
}

function bindProfileNav() {
    const navLinks = document.querySelectorAll('.profile-nav a[data-section], .profile-shortcut[data-section]');
    const sectionLinks = document.querySelectorAll('.profile-nav a[data-section]');
    const sections = document.querySelectorAll('.profile-section');

    function showSection(id) {
        sections.forEach(sec => sec.classList.add('hidden'));
        const target = document.getElementById(id + '-section');
        if (target) target.classList.remove('hidden');
        // Khi mở tab Đơn mua thì tải danh sách đơn hàng
        if (id === 'orders') {
            loadOrders();
        }
        sectionLinks.forEach(l => {
            const secId = l.getAttribute('data-section');
            l.classList.toggle('active', secId === id);
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            if (!sectionId) return;
            showSection(sectionId);
        });
    });

    // Hiển thị mặc định
    showSection('account');
}

function bindProfileForms() {
    // Update profile information
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                name: document.getElementById('profile-name').value,
                email: document.getElementById('profile-email').value,
                phone: document.getElementById('profile-phone').value
            };
            try {
                const res = await fetch('/api/update_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                showNotification(data.message || 'Đã cập nhật thông tin.', 'success');
                // Reload name in nav if needed (not done here)
            } catch (err) {
                showNotification('Không thể cập nhật thông tin.', 'error');
            }
        });
    }
    // Change password
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const oldPassword = document.getElementById('old-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            if (newPassword !== confirmPassword) {
                showNotification('Mật khẩu mới và xác nhận không khớp.', 'error');
                return;
            }
            try {
                const res = await fetch('/api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ old_password: oldPassword, new_password: newPassword })
                });
                const data = await res.json();
                showNotification(data.message || 'Đã đổi mật khẩu.', 'success');
                // Clear password fields
                document.getElementById('old-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            } catch (err) {
                showNotification('Không thể đổi mật khẩu.', 'error');
            }
        });
    }
}

function bindChangeAvatar() {
    const fileInput = document.getElementById('avatar-input');
    const changeBtn = document.getElementById('change-avatar-btn');
    if (!fileInput || !changeBtn) return;
    // Khi nhấn nút "Thay ảnh" thì mở hộp chọn file
    changeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        fileInput.click();
    });
    // Khi người dùng chọn file thì cắt giữa ảnh và tải lên
    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;
        const img = new Image();
        img.onload = async function() {
            // Tính toán vùng cắt trung tâm hình vuông
            const side = Math.min(img.width, img.height);
            const sx = (img.width - side) / 2;
            const sy = (img.height - side) / 2;
            // Vẽ lên canvas
            const canvas = document.createElement('canvas');
            canvas.width = side;
            canvas.height = side;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, sx, sy, side, side, 0, 0, side, side);
            // Hiển thị preview của ảnh sau khi cắt
            const preview = document.getElementById('avatar-preview');
            if (preview) {
                preview.src = canvas.toDataURL('image/png');
            }
            // Chuyển canvas thành Blob để tải lên
            canvas.toBlob(async (blob) => {
                const formData = new FormData();
                // Sử dụng tên file gốc cho blob nếu có
                formData.append('avatar', blob, file.name);
                try {
                    const res = await fetch('/api/upload_avatar.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                        if (data.success) {
                            // Sau khi tải lên thành công, cập nhật preview bằng file đã lưu trong server
                            preview.src = '/assets/images/avatars/' + data.avatar;
                            showNotification('Đã cập nhật ảnh đại diện.', 'success');
                        } else {
                            showNotification(data.message || 'Lỗi tải ảnh', 'error');
                        }
                    } catch (err) {
                        showNotification('Không thể tải ảnh.', 'error');
                    }
            }, 'image/png');
        };
        img.onerror = function() {
            showNotification('Không thể đọc ảnh.', 'error');
        };
        img.src = URL.createObjectURL(file);
    });
}

async function loadAddresses() {
    try {
        const res = await fetch('/api/get_addresses.php');
        const list = await res.json();
        const container = document.getElementById('address-list');
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = '<p>Chưa có địa chỉ giao hàng.</p>';
            const addressStat = document.getElementById('stat-address-count');
            if (addressStat) addressStat.textContent = '0';
            return;
        }
        const addressStat = document.getElementById('stat-address-count');
        if (addressStat) addressStat.textContent = list.length;
        list.forEach((addr) => {
            const div = document.createElement('div');
            div.className = 'address-item';
            div.dataset.id = addr.id;
            div.dataset.recipient = addr.recipient_name || '';
            div.dataset.email = addr.email || '';
            div.dataset.phone = addr.phone || '';
            div.dataset.city = addr.city || '';
            div.dataset.district = addr.district || '';
            div.dataset.address = addr.address || '';

            const isDefault = Number(addr.is_default) === 1;

            div.innerHTML = `
                <div class="address-row">
                    <div class="address-main">
                        <p class="address-recipient">
                            <strong>${addr.recipient_name}</strong>
                            ${addr.phone ? `<span class="address-phone">(+84) ${addr.phone}</span>` : ''}
                        </p>
                        <p class="address-text">${addr.address}</p>
                    </div>
                    <div class="address-actions">
                        ${isDefault ? '<span class="address-default-badge">Mặc định</span>' : ''}
                        <button class="btn-link btn-edit-address">Cập nhật</button>
                        <button class="btn-link btn-delete-address">Xóa</button>
                        ${!isDefault ? '<button class="btn-outline btn-set-default">Thiết lập mặc định</button>' : ''}
                    </div>
                </div>
            `;
            container.appendChild(div);
        });

        bindAddressItemEvents();
    } catch (err) {
        console.error(err);
    }
}

let currentAddressId = null;

function setupAddressModal() {
    const openBtn = document.getElementById('open-address-modal');
    const overlay = document.getElementById('address-modal-overlay');
    const closeBtn = document.getElementById('close-address-modal');
    const cancelBtn = document.getElementById('cancel-address-modal');
    const form = document.getElementById('address-form');
    const titleEl = document.getElementById('address-modal-title');

    if (!overlay || !form) return;

    // Cho phép chỗ khác mở modal qua custom event (dùng cho nút Cập nhật)
    document.addEventListener('openAddressModal', (e) => {
        const { mode, addr } = e.detail || {};
        openModal(mode, addr);
    });

    function openModal(mode, addr) {
        overlay.classList.remove('hidden');
        document.body.classList.add('no-scroll');
        if (mode === 'edit' && addr) {
            titleEl.textContent = 'Cập nhật địa chỉ';
            currentAddressId = addr.id;
            document.getElementById('addr-recipient').value = addr.recipient || '';
            document.getElementById('addr-email').value = addr.email || '';
            document.getElementById('addr-phone').value = addr.phone || '';
            document.getElementById('addr-city').value = addr.city || '';
            const event = new Event('change');
            document.getElementById('addr-city').dispatchEvent(event);
            document.getElementById('addr-district').value = addr.district || '';
            document.getElementById('addr-address').value = addr.address || '';
        } else {
            titleEl.textContent = 'Thêm địa chỉ mới';
            currentAddressId = null;
            form.reset();
        }
    }

    function closeModal() {
        overlay.classList.add('hidden');
        document.body.classList.remove('no-scroll');
        currentAddressId = null;
    }

    if (openBtn) {
        openBtn.addEventListener('click', () => openModal('add'));
    }
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            recipient_name: document.getElementById('addr-recipient').value.trim(),
            email: document.getElementById('addr-email').value.trim(),
            phone: document.getElementById('addr-phone').value.trim(),
            city: document.getElementById('addr-city').value.trim(),
            district: document.getElementById('addr-district').value.trim(),
            address: document.getElementById('addr-address').value.trim()
        };

        const url = currentAddressId ? '/api/update_address.php' : '/api/add_address.php';
        if (currentAddressId) {
            payload.id = currentAddressId;
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                showNotification(data.error || 'Không thể lưu địa chỉ', 'error');
                return;
            }
            showNotification(data.message || 'Đã lưu địa chỉ.', 'success');
            closeModal();
            loadAddresses();
        } catch (err) {
            console.error(err);
            showNotification('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    });
}

function bindAddressItemEvents() {
    document.querySelectorAll('.btn-delete-address').forEach(btn => {
            btn.addEventListener('click', async () => {
            const wrapper = btn.closest('.address-item');
            const id = wrapper?.dataset.id;
            if (!id) return;
                if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;
                try {
                    const res = await fetch('/api/delete_address.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: Number(id) })
                    });
                    const data = await res.json();
                if (res.ok && data.success) {
                        showNotification(data.message || 'Đã xóa địa chỉ.', 'success');
                        loadAddresses();
                    } else {
                        showNotification(data.error || 'Không thể xóa địa chỉ', 'error');
                    }
                } catch (err) {
                console.error(err);
                    showNotification('Lỗi xóa địa chỉ', 'error');
                }
            });
        });

    document.querySelectorAll('.btn-edit-address').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = btn.closest('.address-item');
            if (!wrapper) return;
            const addr = {
                id: wrapper.dataset.id,
                recipient: wrapper.dataset.recipient,
                email: wrapper.dataset.email,
                phone: wrapper.dataset.phone,
                city: wrapper.dataset.city,
                district: wrapper.dataset.district,
                address: wrapper.dataset.address
            };
            const openEvent = new CustomEvent('openAddressModal', { detail: { mode: 'edit', addr } });
            document.dispatchEvent(openEvent);
        });
    });

    // Thiết lập mặc định chỉ thay đổi thứ tự hiển thị trên giao diện
    document.querySelectorAll('.btn-set-default').forEach(btn => {
        btn.addEventListener('click', async () => {
            const wrapper = btn.closest('.address-item');
            const id = wrapper?.dataset.id;
            if (!wrapper || !id) return;
            try {
                const res = await fetch('/api/set_default_address.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: Number(id) })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showNotification(data.message || 'Đã đặt địa chỉ mặc định.', 'success');
                    // Reload lại danh sách để cập nhật badge/nút theo dữ liệu mới
                    loadAddresses();
                } else {
                    showNotification(data.error || 'Không thể đặt địa chỉ mặc định', 'error');
                }
    } catch (err) {
        console.error(err);
                showNotification('Lỗi khi đặt địa chỉ mặc định', 'error');
    }
        });
    });
}

async function loadVouchers() {
    try {
        const res = await fetch('/api/get_vouchers.php');
        const vouchers = await res.json();
        const container = document.getElementById('voucher-list');
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(vouchers) || vouchers.length === 0) {
            container.innerHTML = '<p>Không có voucher khả dụng.</p>';
            const voucherStat = document.getElementById('stat-voucher-count');
            if (voucherStat) voucherStat.textContent = '0';
            return;
        }
        const voucherStat = document.getElementById('stat-voucher-count');
        if (voucherStat) voucherStat.textContent = vouchers.length;
        vouchers.forEach(v => {
            const div = document.createElement('div');
            div.className = 'voucher-item';
            let discountText = v.discount_type === 'percent' ? `${v.discount_value}%` : `${Number(v.discount_value).toLocaleString()}₫`;
            div.innerHTML = `<p><strong>${v.code}</strong> - Giảm: ${discountText} - HSD: ${v.expiration_date}</p>`;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
        const container = document.getElementById('voucher-list');
        if (container) container.innerHTML = '<p>Lỗi khi tải voucher.</p>';
        const voucherStat = document.getElementById('stat-voucher-count');
        if (voucherStat) voucherStat.textContent = '--';
    }
}

async function updateOrderStat() {
    try {
        const res = await fetch('/api/get_order_history.php');
        const orders = await res.json();
        const orderStat = document.getElementById('stat-order-count');
        if (orderStat && Array.isArray(orders)) {
            orderStat.textContent = orders.length;
        }
    } catch (err) {
        console.error(err);
    }
}

async function loadOrders() {

    const container = document.getElementById('orders');
    if (!container) return;
}

// (Duplicate loadAddresses removed to avoid overriding earlier definition)

async function loadSavedList() {
    try {
        const res = await fetch('/api/get_saved_list.php');
        const items = await res.json();
        const container = document.getElementById('saved-list');
        container.innerHTML = '';
        if (items.length === 0) {
            container.innerHTML = '<p>Không có sản phẩm nào lưu.</p>';
            return;
        }
        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'saved-item';
            div.innerHTML = `${item.name} - ${Number(item.price).toLocaleString()}₫ (x${item.quantity})`;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
}
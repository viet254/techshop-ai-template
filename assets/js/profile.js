// JS for profile page
document.addEventListener('DOMContentLoaded', async () => {
    await loadProfile();
    bindProfileNav();
    bindProfileForms();
    loadAddresses();

    const addressForm = document.getElementById('address-form');
    if (addressForm) {
        addressForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const recipientName = document.getElementById('addr-recipient').value.trim();
            const email         = document.getElementById('addr-email').value.trim();
            const phone         = document.getElementById('addr-phone').value.trim();
            const city          = document.getElementById('addr-city').value.trim();
            const district      = document.getElementById('addr-district').value.trim();
            const address       = document.getElementById('addr-address').value.trim();

            try {
                const res = await fetch('/api/add_address.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        recipient_name: recipientName,
                        email,
                        phone,
                        city,
                        district,
                        address
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    showNotification(data.error || 'Không thể thêm địa chỉ', 'error');
                    return;
                }
                showNotification(data.message || 'Đã thêm địa chỉ.', 'success');
                addressForm.reset();
                loadAddresses();
            } catch (err) {
                console.error(err);
                showNotification('Có lỗi xảy ra, vui lòng thử lại.', 'error');
            }
        });
    }

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
            if (nameDisplay) nameDisplay.textContent = user.name || 'Người dùng';
            if (emailDisplay) emailDisplay.textContent = user.email || 'Chưa có email';
            if (phoneDisplay) phoneDisplay.textContent = user.phone || 'Chưa cập nhật số điện thoại';
            const avatarPreview = document.getElementById('avatar-preview');
            if (user.avatar) {
                avatarPreview.src = '/assets/images/avatars/' + user.avatar;
            } else {
                avatarPreview.src = '/assets/images/default-avatar.png';
            }
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
        list.forEach(addr => {
            const div = document.createElement('div');
            div.className = 'address-item';
            div.innerHTML = `
                <p><strong>${addr.recipient_name}</strong> - ${addr.phone || ''}</p>
                <p>${addr.address}</p>
                <button data-id="${addr.id}" class="delete-address">Xóa</button>
            `;
            container.appendChild(div);
        });
        document.querySelectorAll('.delete-address').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;
                try {
                    const res = await fetch('/api/delete_address.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        // Show success notification and reload list
                        showNotification(data.message || 'Đã xóa địa chỉ.', 'success');
                        loadAddresses();
                    } else {
                        showNotification(data.error || 'Không thể xóa địa chỉ', 'error');
                    }
                } catch (err) {
                    showNotification('Lỗi xóa địa chỉ', 'error');
                }
            });
        });
    } catch (err) {
        console.error(err);
    }
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
    try {
        const res = await fetch('/api/get_order_history.php');
        const orders = await res.json();
        const container = document.getElementById('orders');
        container.innerHTML = '';
        if (orders.length === 0) {
            container.innerHTML = '<p>Bạn chưa có đơn hàng nào.</p>';
            return;
        }
        orders.forEach(order => {
            const div = document.createElement('div');
            div.className = 'order-item';
            // Liên kết tới trang chi tiết đơn hàng
            const link = `/order_detail.php?id=${order.id}`;
            div.innerHTML = `
                <p>Mã đơn: <a href="${link}">#${order.id}</a> | Tổng: ${Number(order.final_total).toLocaleString()}₫ | Trạng thái: ${order.status}</p>
            `;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
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
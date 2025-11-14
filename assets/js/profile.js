// JS for profile page
document.addEventListener('DOMContentLoaded', async () => {
    // Load profile info including avatar
    await loadProfile();
    // Bind section navigation
    bindProfileNav();
    // Bind profile update and password change
    bindProfileForms();
    // Load addresses
    loadAddresses();
    // Bind address form
    const addressForm = document.getElementById('address-form');
    if (addressForm) {
        addressForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const recipient = document.getElementById('addr-recipient').value.trim();
            const phone = document.getElementById('addr-phone').value.trim();
            const address = document.getElementById('addr-address').value.trim();
            if (!recipient || !address) {
                alert('Tên người nhận và địa chỉ là bắt buộc.');
                return;
            }
            try {
                const res = await fetch('/techshop-ai-template/api/add_address.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ recipient_name: recipient, phone: phone, address: address })
                });
                const data = await res.json();
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('addr-recipient').value = '';
                    document.getElementById('addr-phone').value = '';
                    document.getElementById('addr-address').value = '';
                    loadAddresses();
                }
            } catch (err) {
                alert('Không thể thêm địa chỉ');
            }
        });
    }
    // Load vouchers
    loadVouchers();
    // Bind avatar upload
    bindAvatarUpload();
});

async function loadProfile() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_profile.php');
        const user = await res.json();
        if (user) {
            document.getElementById('profile-name').value = user.name || '';
            document.getElementById('profile-email').value = user.email || '';
            document.getElementById('profile-phone').value = user.phone || '';
            const avatarPreview = document.getElementById('avatar-preview');
            if (user.avatar) {
                avatarPreview.src = '/techshop-ai-template/assets/images/avatars/' + user.avatar;
            } else {
                avatarPreview.src = '/techshop-ai-template/assets/images/default-avatar.png';
            }
        }
    } catch (err) {
        console.error(err);
    }
}

function bindProfileNav() {
    const navLinks = document.querySelectorAll('.profile-nav a[data-section]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            const sectionId = this.getAttribute('data-section');
            // Hide all sections
            document.querySelectorAll('.profile-section').forEach(sec => sec.classList.add('hidden'));
            // Show selected section
            document.getElementById(sectionId + '-section').classList.remove('hidden');
        });
    });
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
                const res = await fetch('/techshop-ai-template/api/update_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                alert(data.message || 'Đã cập nhật thông tin.');
                // Reload name in nav if needed (not done here)
            } catch (err) {
                alert('Không thể cập nhật thông tin.');
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
                alert('Mật khẩu mới và xác nhận không khớp.');
                return;
            }
            try {
                const res = await fetch('/techshop-ai-template/api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ old_password: oldPassword, new_password: newPassword })
                });
                const data = await res.json();
                alert(data.message || 'Đã đổi mật khẩu.');
                // Clear password fields
                document.getElementById('old-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            } catch (err) {
                alert('Không thể đổi mật khẩu.');
            }
        });
    }
}

function bindAvatarUpload() {
    const avatarInput = document.getElementById('avatar-input');
    const uploadBtn = document.getElementById('upload-avatar-btn');
    uploadBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        const file = avatarInput.files[0];
        if (!file) {
            alert('Hãy chọn ảnh trước.');
            return;
        }
        const formData = new FormData();
        formData.append('avatar', file);
        try {
            const res = await fetch('/techshop-ai-template/api/upload_avatar.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('avatar-preview').src = '/techshop-ai-template/assets/images/avatars/' + data.avatar;
                alert('Đã cập nhật ảnh đại diện.');
            } else {
                alert(data.message || 'Lỗi tải ảnh');
            }
        } catch (err) {
            alert('Không thể tải ảnh.');
        }
    });
}

async function loadAddresses() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_addresses.php');
        const list = await res.json();
        const container = document.getElementById('address-list');
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = '<p>Chưa có địa chỉ giao hàng.</p>';
            return;
        }
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
                    const res = await fetch('/techshop-ai-template/api/delete_address.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        loadAddresses();
                    } else {
                        alert(data.error || 'Không thể xóa địa chỉ');
                    }
                } catch (err) {
                    alert('Lỗi xóa địa chỉ');
                }
            });
        });
    } catch (err) {
        console.error(err);
    }
}

async function loadVouchers() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_vouchers.php');
        const vouchers = await res.json();
        const container = document.getElementById('voucher-list');
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(vouchers) || vouchers.length === 0) {
            container.innerHTML = '<p>Không có voucher khả dụng.</p>';
            return;
        }
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
    }
}

async function loadOrders() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_order_history.php');
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
            const link = `/techshop-ai-template/order_detail.php?id=${order.id}`;
            div.innerHTML = `
                <p>Mã đơn: <a href="${link}">#${order.id}</a> | Tổng: ${Number(order.final_total).toLocaleString()}₫ | Trạng thái: ${order.status}</p>
            `;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
}

async function loadAddresses() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_addresses.php');
        const list = await res.json();
        const container = document.getElementById('address-list');
        container.innerHTML = '';
        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = '<p>Chưa có địa chỉ giao hàng.</p>';
            return;
        }
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
        // Gắn sự kiện xoá
        document.querySelectorAll('.delete-address').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;
                try {
                    const res = await fetch('/techshop-ai-template/api/delete_address.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        loadAddresses();
                    } else {
                        alert(data.error || 'Không thể xóa địa chỉ');
                    }
                } catch (err) {
                    alert('Lỗi xóa địa chỉ');
                }
            });
        });
    } catch (err) {
        console.error(err);
    }
}

async function loadSavedList() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_saved_list.php');
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
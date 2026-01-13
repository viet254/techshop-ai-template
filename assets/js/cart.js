// JS for cart page
// Biến toàn cục lưu phương thức thanh toán được chọn
let selectedPaymentMethod = 'cod';
// Biến toàn cục lưu địa chỉ giao hàng đang chọn (dùng cho checkout)
let selectedAddressId = 0;
// Cache danh sách địa chỉ cho modal chọn địa chỉ
let cartAddresses = [];
// Bản đồ tỉnh/thành -> quận/huyện dùng cho form thêm địa chỉ trong giỏ hàng (giống profile)
const addressDistrictsByProvince = {
    "Ha Noi": [
        "Ba Đình", "Hoàn Kiếm", "Hai Bà Trưng", "Đống Đa", "Tây Hồ", "Cầu Giấy",
        "Thanh Xuân", "Long Biên", "Nam Từ Liêm", "Bắc Từ Liêm", "Hà Đông"
    ],
    "Bac Ninh": [
        "TP Bắc Ninh", "Từ Sơn", "Yên Phong", "Tiên Du", "Thuận Thành",
        "Gia Bình", "Quế Võ", "Lương Tài"
    ],
    "Hai Phong": [
        "Hồng Bàng", "Ngô Quyền", "Lê Chân", "Kiến An", "Hải An",
        "Đồ Sơn", "An Dương", "An Lão", "Kiến Thụy"
    ],
    "Quang Ninh": [
        "Hạ Long", "Cẩm Phả", "Uông Bí", "Móng Cái", "Quảng Yên",
        "Đông Triều", "Vân Đồn", "Tiên Yên"
    ],
    "Bac Giang": [
        "TP Bắc Giang", "Yên Thế", "Tân Yên", "Lục Nam", "Lục Ngạn",
        "Sơn Động", "Yên Dũng", "Việt Yên", "Lạng Giang", "Hiệp Hòa"
    ],
    "Thai Nguyen": [
        "TP Thái Nguyên", "Sông Công", "Định Hóa", "Phú Lương",
        "Đồng Hỷ", "Võ Nhai", "Phú Bình"
    ],
    "Vinh Phuc": [
        "TP Vĩnh Yên", "Phúc Yên", "Yên Lạc", "Vĩnh Tường",
        "Tam Dương", "Tam Đảo", "Sông Lô", "Bình Xuyên"
    ],
    "Nam Dinh": [
        "TP Nam Định", "Mỹ Lộc", "Vụ Bản", "Ý Yên", "Trực Ninh",
        "Xuân Trường", "Giao Thủy", "Nghĩa Hưng", "Nam Trực", "Hải Hậu"
    ],
    "Ninh Binh": [
        "TP Ninh Bình", "Tam Điệp", "Gia Viễn", "Hoa Lư",
        "Yên Khánh", "Yên Mô", "Kim Sơn", "Nho Quan"
    ],
    "Ha Nam": [
        "TP Phủ Lý", "Duy Tiên", "Lý Nhân", "Kim Bảng",
        "Thanh Liêm", "Bình Lục"
    ],
    "Hai Duong": [
        "TP Hải Dương", "Chí Linh", "Nam Sách", "Kinh Môn",
        "Thanh Hà", "Cẩm Giàng", "Gia Lộc", "Tứ Kỳ",
        "Ninh Giang", "Thanh Miện"
    ]
};

const currencyFormatter = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
let currentCartSubtotal = 0;

document.addEventListener('DOMContentLoaded', async () => {
    loadCart();
    // Load addresses for selection (if user logged in)
    loadAddressOptions();
    // Initialize payment method UI
    initPaymentMethod();
    // Init voucher input
    initVoucher();
    // Xử lý thanh toán
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            // Kiểm tra địa chỉ trước khi thanh toán
            let addressId = selectedAddressId || 0;
            
            // Nếu chưa có địa chỉ, chuyển đến trang profile để nhập địa chỉ
            if (addressId <= 0) {
                const confirmRedirect = confirm('Bạn chưa có địa chỉ giao hàng. Vui lòng thêm địa chỉ trước khi thanh toán.\n\nBạn có muốn chuyển đến trang cá nhân để thêm địa chỉ không?');
                if (confirmRedirect) {
                    window.location.href = '/profile.php';
                }
                return;
            }
            
            // Kiểm tra phương thức thanh toán - không cho phép thanh toán qua ngân hàng
            const paymentMethod = selectedPaymentMethod || 'cod';
            if (paymentMethod === 'bank') {
                showNotification('Đang bảo trì cổng thanh toán. Vui lòng chọn phương thức thanh toán khác.', 'error');
                // Tự động chuyển về COD
                selectedPaymentMethod = 'cod';
                const codRadio = document.querySelector('input[name="payment-method"][value="cod"]');
                if (codRadio) {
                    codRadio.checked = true;
                }
                return;
            }
            
            const voucherCode = appliedVoucherCode || '';
            try {
                const res = await fetch('/api/checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ address_id: addressId, voucher_code: voucherCode, payment_method: paymentMethod })
                });
                const data = await res.json();
                if (data.success) {
                    // Show success notification with order details
                    const message = 'Đã đặt đơn hàng thành công!\nMã đơn: ' + data.order_id + '\nThành tiền: ' + Number(data.final_total).toLocaleString() + '₫';
                    showNotification(message, 'success');
                    // Reset voucher & address selection
                    appliedVoucherCode = '';
                    discountAmount = 0;
                    loadCart();
                    loadAddressOptions();
                    initPaymentMethod();
                    updateTotals(0);
                } else {
                    showNotification(data.message || 'Thanh toán thất bại.', 'error');
                }
            } catch (err) {
                showNotification('Không thể thanh toán.', 'error');
            }
        });
    }
});

async function loadCart() {
    try {
        const res = await fetch('/api/get_cart.php');
        const data = await res.json();
        const cartItems = data.items || [];
        const savedItems = data.saved || [];
        const cartTable = document.getElementById('cart-items');
        const savedTable = document.getElementById('saved-items');
        cartTable.innerHTML = '';
        savedTable.innerHTML = '';
        if (cartItems.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `<td colspan="5" style="text-align:center;">Giỏ hàng của bạn đang trống.</td>`;
            cartTable.appendChild(emptyRow);
        } else {
            cartItems.forEach(item => {
                const row = document.createElement('tr');
                const priceHtml = buildPriceHtml(item);
                const warningHtml = buildStockWarning(item);
                row.innerHTML = `
                    <td>
                        <div class="cart-item-info">
                            ${item.image ? `<img src="assets/images/${item.image}" alt="${item.name}" />` : ''}
                            <div>
                                <strong>${item.name}</strong>
                                ${warningHtml}
                            </div>
                        </div>
                    </td>
                    <td>${priceHtml}</td>
                    <td>
                        <div class="qty-control">
                            <input type="number" min="1" ${item.max_quantity ? `max="${item.max_quantity}"` : ''} value="${item.quantity}" data-id="${item.product_id}" onchange="updateQuantity(this)" />
                        </div>
                    </td>
                    <td>${formatCurrency(item.line_total)}</td>
                    <td>
                        <button onclick="saveForLater(${item.product_id})">Yêu thích</button>
                        <button onclick="removeItem(${item.product_id}, false)">Xóa</button>
                    </td>
                `;
                cartTable.appendChild(row);
            });
        }
        if (savedItems.length === 0) {
            const emptySaved = document.createElement('tr');
            emptySaved.innerHTML = `<td colspan="4" style="text-align:center;">Chưa có sản phẩm yêu thích nào.</td>`;
            savedTable.appendChild(emptySaved);
        } else {
            savedItems.forEach(item => {
                const row = document.createElement('tr');
                const priceHtml = buildPriceHtml(item, true);
                row.innerHTML = `
                    <td>
                        <div class="cart-item-info">
                            ${item.image ? `<img src="assets/images/${item.image}" alt="${item.name}" />` : ''}
                            <div><strong>${item.name}</strong></div>
                        </div>
                    </td>
                    <td>${priceHtml}</td>
                    <td>${item.quantity}</td>
                    <td>
                        <button onclick="moveToCart(${item.product_id})">Mua ngay</button>
                        <button onclick="removeItem(${item.product_id}, true)">Xóa</button>
                    </td>
                `;
                savedTable.appendChild(row);
            });
        }
        currentCartSubtotal = data.summary ? Number(data.summary.subtotal) || 0 : 0;
        document.getElementById('cart-total').textContent = formatCurrency(currentCartSubtotal);
        updateTotals(currentCartSubtotal);
        // Update cart count in header + nav
        const lineCount = data.summary ? data.summary.line_count : cartItems.length;
        const text = lineCount > 0 ? `(${lineCount})` : '';
        const cartCount = document.getElementById('cart-count');
        const navCartCount = document.getElementById('nav-cart-count');
        if (cartCount) cartCount.textContent = text;
        if (navCartCount) navCartCount.textContent = text;
    } catch (err) {
        console.error(err);
    }
}

// Bind sự kiện đổi Tỉnh/Thành phố -> load Quận/Huyện cho form địa chỉ trong giỏ hàng
function bindCartCityDistrictSelects(rootEl = document) {
    const citySelect = rootEl.querySelector('#addr-city');
    const districtSelect = rootEl.querySelector('#addr-district');
    if (!citySelect || !districtSelect) return;

    // Tránh bind lặp nhiều lần khi mở modal nhiều lần
    if (citySelect.dataset.bound === '1') return;
    citySelect.dataset.bound = '1';

    const renderDistricts = () => {
        const val = citySelect.value;
        districtSelect.innerHTML = '<option value="">--- Chọn quận/huyện ---</option>';
        const districts = addressDistrictsByProvince[val] || [];
        districts.forEach((d) => {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            districtSelect.appendChild(opt);
        });
    };

    citySelect.addEventListener('change', renderDistricts);
}

async function updateQuantity(input) {
    const id = input.dataset.id;
    const quantity = parseInt(input.value);
    try {
        const res = await fetch('/api/update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, quantity })
        });
        const data = await res.json();
        if (data && data.message) {
            showNotification(data.message, data.success ? 'success' : 'error');
        }
        loadCart();
    } catch (err) {
        console.error(err);
    }
}

async function saveForLater(productId) {
    try {
        await fetch('/api/save_for_later.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: productId })
        });
        loadCart();
    } catch (err) {
        console.error(err);
    }
}

async function moveToCart(productId) {
    try {
        await fetch('/api/move_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: productId })
        });
        loadCart();
    } catch (err) {
        console.error(err);
    }
}

async function removeItem(productId, saved) {
    try {
        await fetch('/api/remove_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: productId, saved })
        });
        loadCart();
    } catch (err) {
        console.error(err);
    }
}

// Biến toàn cục lưu mã voucher đã áp dụng và số tiền giảm
let appliedVoucherCode = '';
let discountAmount = 0;

// Load danh sách địa chỉ và hiển thị địa chỉ đang chọn + modal "Thay đổi"
async function loadAddressOptions() {
    const container = document.getElementById('address-select-container');
    if (!container) return;
    container.innerHTML = '';
    try {
        const res = await fetch('/api/get_addresses.php');
        const addresses = await res.json();
        cartAddresses = Array.isArray(addresses) ? addresses : [];
        if (!Array.isArray(addresses) || addresses.length === 0) {
            container.innerHTML = '<p style="color: #e53935; font-weight: 500;">⚠️ Bạn chưa có địa chỉ giao hàng. <a href="/profile.php" style="color: #1a73e8; text-decoration: underline;">Vui lòng thêm địa chỉ tại đây</a> trước khi thanh toán.</p>';
            return;
        }
        // Nếu đã có selectedAddressId (ví dụ vừa thêm địa chỉ mới), ưu tiên dùng nó
        let currentAddr = null;
        if (selectedAddressId) {
            currentAddr = addresses.find(a => Number(a.id) === Number(selectedAddressId)) || null;
        }
        // Nếu chưa có hoặc không tìm thấy thì dùng địa chỉ mặc định, hoặc địa chỉ đầu tiên
        if (!currentAddr) {
            currentAddr = addresses.find(a => Number(a.is_default) === 1) || addresses[0];
        }
        selectedAddressId = currentAddr ? Number(currentAddr.id) : 0;

        renderCartAddressSummary(currentAddr);

        // Gắn sự kiện mở modal "Thay đổi"
        const changeBtn = document.getElementById('cart-change-address-btn');
        if (changeBtn) {
            changeBtn.addEventListener('click', openCartAddressModal);
        }
    } catch (err) {
        container.innerHTML = '<p>Không thể tải địa chỉ.</p>';
    }
}

function renderCartAddressSummary(addr) {
    const container = document.getElementById('address-select-container');
    if (!container || !addr) return;
    const phoneText = addr.phone ? `(+84) ${addr.phone}` : '';
    const isDefault = Number(addr.is_default) === 1;
    container.innerHTML = `
        <div class="cart-address-summary">
            <div class="cart-address-label">Địa Chỉ Nhận Hàng</div>
            <div class="cart-address-main">
                <strong>${addr.recipient_name}</strong> ${phoneText ? `<span>${phoneText}</span>` : ''} -
                <span>${addr.address}</span>
                ${isDefault ? '<span class="address-default-badge" style="margin-left:6px;">Mặc định</span>' : ''}
            </div>
            <div class="cart-address-change">
                <button type="button" id="cart-change-address-btn" class="btn-link">Thay đổi</button>
            </div>
        </div>
    `;

    // Gắn lại sự kiện "Thay đổi" mỗi lần render tóm tắt
    const changeBtn = document.getElementById('cart-change-address-btn');
    if (changeBtn) {
        changeBtn.addEventListener('click', openCartAddressModal);
    }
}

async function openCartAddressModal() {
    const overlay = document.getElementById('cart-address-modal-overlay');
    const listEl = document.getElementById('cart-address-list');
    const form = document.getElementById('cart-address-form');
    if (!overlay || !listEl) return;

    // Refetch địa chỉ mới nhất mỗi lần mở modal
    try {
        const res = await fetch('/api/get_addresses.php');
        const addresses = await res.json();
        cartAddresses = Array.isArray(addresses) ? addresses : [];
    } catch (err) {
        console.error(err);
        cartAddresses = [];
    }

    // Đảm bảo modal luôn bắt đầu ở chế độ danh sách
    const titleEl = document.querySelector('.cart-address-modal .address-modal-header h4');
    const confirmBtn = document.getElementById('cart-address-modal-confirm');
    const toggleAddBtn = document.getElementById('cart-address-add-toggle');
    let isAddMode = false;

    if (titleEl) titleEl.textContent = 'Địa Chỉ Của Tôi';
    if (confirmBtn) confirmBtn.textContent = 'Xác nhận';
    if (toggleAddBtn) toggleAddBtn.textContent = '+ Thêm Địa Chỉ Mới';
    if (form) {
        form.classList.add('hidden');
        form.reset();
    }

    // Đảm bảo danh sách luôn hiển thị lại (có thể đã bị ẩn ở lần trước trong chế độ thêm mới)
    listEl.style.display = '';
    listEl.innerHTML = '';
    if (!Array.isArray(cartAddresses) || cartAddresses.length === 0) {
        listEl.innerHTML = '<p>Chưa có địa chỉ giao hàng.</p>';
    } else {
        cartAddresses.forEach(addr => {
            const row = document.createElement('div');
            row.className = 'address-item';
            row.dataset.id = addr.id;
            row.dataset.recipient = addr.recipient_name || '';
            row.dataset.phone = addr.phone || '';
            row.dataset.address = addr.address || '';
            row.innerHTML = `
                <label class="address-row">
                    <div style="margin-right:10px;">
                        <input type="radio" name="cart-address-radio" value="${addr.id}" ${Number(addr.id) === selectedAddressId ? 'checked' : ''} />
                    </div>
                    <div class="address-main">
                        <p class="address-recipient">
                            <strong>${addr.recipient_name}</strong>
                            ${addr.phone ? `<span class="address-phone">(+84) ${addr.phone}</span>` : ''}
                        </p>
                        <p class="address-text">${addr.address}</p>
                    </div>
                    <div class="address-actions">
                        ${Number(addr.is_default) === 1 ? '<span class="address-default-badge">Mặc định</span>' : ''}
                    </div>
                </label>
            `;
            listEl.appendChild(row);
        });
    }

    overlay.classList.remove('hidden');

    const closeBtn = document.getElementById('cart-address-modal-close');
    const cancelBtn = document.getElementById('cart-address-modal-cancel');

    function switchToListMode() {
        isAddMode = false;
        if (titleEl) titleEl.textContent = 'Địa Chỉ Của Tôi';
        if (listEl) listEl.style.display = '';
        if (toggleAddBtn) toggleAddBtn.textContent = '+ Thêm Địa Chỉ Mới';
        if (form) {
            form.classList.add('hidden');
            form.reset();
        }
        if (confirmBtn) confirmBtn.textContent = 'Xác nhận';
    }

    function switchToAddMode() {
        isAddMode = true;
        if (titleEl) titleEl.textContent = 'Thêm Địa Chỉ Mới';
        if (listEl) listEl.style.display = 'none';
        if (toggleAddBtn) toggleAddBtn.textContent = '← Quay lại danh sách';
        if (form) form.classList.remove('hidden');
        if (confirmBtn) confirmBtn.textContent = 'Lưu';
    }

    function closeModal() {
        overlay.classList.add('hidden');
    }

    if (closeBtn) closeBtn.onclick = closeModal;
    if (cancelBtn) {
        cancelBtn.onclick = () => {
            if (isAddMode) {
                // Từ màn hình thêm địa chỉ quay lại danh sách
                switchToListMode();
            } else {
                closeModal();
            }
        };
    }
    overlay.onclick = (e) => {
        if (e.target === overlay) closeModal();
    };

    if (toggleAddBtn && form) {
        toggleAddBtn.onclick = () => {
            if (isAddMode) {
                switchToListMode();
            } else {
                switchToAddMode();
                // Bind tỉnh/thành -> quận/huyện cho form thêm địa chỉ trong modal
                bindCartCityDistrictSelects(overlay);
            }
        };
    }

    if (confirmBtn) {
        confirmBtn.onclick = async () => {
            if (isAddMode) {
                // Lưu địa chỉ mới (dùng cùng ID với form Sổ địa chỉ ở trang cá nhân)
                const recipient = overlay.querySelector('#addr-recipient')?.value.trim() || '';
                const email = overlay.querySelector('#addr-email')?.value.trim() || '';
                const phone = overlay.querySelector('#addr-phone')?.value.trim() || '';
                const fullAddress = overlay.querySelector('#addr-address')?.value.trim() || '';
                const city = overlay.querySelector('#addr-city')?.value.trim() || '';
                const district = overlay.querySelector('#addr-district')?.value.trim() || '';
                if (!recipient || !email || !fullAddress || !city) {
                    showNotification('Vui lòng nhập đầy đủ tên, email, địa chỉ và tỉnh/thành.', 'error');
                    return;
                }
                try {
                    const res = await fetch('/api/add_address.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            recipient_name: recipient,
                            email,
                            phone,
                            city,
                            district,
                            address: fullAddress
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        showNotification('Đã thêm địa chỉ mới.', 'success');
                        selectedAddressId = Number(data.id) || selectedAddressId;
                        closeModal();
                        loadAddressOptions();
                    } else {
                        showNotification(data.error || 'Không thể thêm địa chỉ', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showNotification('Có lỗi xảy ra khi thêm địa chỉ.', 'error');
                }
            } else {
                // Xác nhận chọn địa chỉ hiện có
                const checked = document.querySelector('input[name="cart-address-radio"]:checked');
                if (checked) {
                    const id = Number(checked.value);
                    const addr = cartAddresses.find(a => Number(a.id) === id);
                    if (addr) {
                        selectedAddressId = id;
                        renderCartAddressSummary(addr);
                    }
                }
                closeModal();
            }
        };
    }
}

// Khởi tạo ô nhập voucher và nút áp dụng
function initVoucher() {
    const container = document.getElementById('voucher-container');
    if (!container) return;
    container.innerHTML = '';
    // Tiêu đề
    const title = document.createElement('p');
    title.textContent = 'Chọn mã giảm giá:';
    title.style.marginBottom = '8px';
    container.appendChild(title);
    // Select voucher list
    const select = document.createElement('select');
    select.id = 'voucher-select';
    // Option mặc định
    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = '-- Không sử dụng --';
    select.appendChild(defaultOpt);
    container.appendChild(select);
    // Tải danh sách voucher từ API
    fetch('/api/get_vouchers.php')
        .then(res => res.json())
        .then(list => {
            if (Array.isArray(list) && list.length > 0) {
                list.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.code;
                    // Hiển thị mô tả giảm giá
                    let desc = '';
                    if (v.discount_type === 'percent') {
                        desc = `Giảm ${v.discount_value}%`;
                    } else {
                        desc = `Giảm ${Number(v.discount_value).toLocaleString()}₫`;
                    }
                    opt.textContent = `${v.code} (${desc})`;
                    select.appendChild(opt);
                });
            } else {
                // Không có voucher
                const noOpt = document.createElement('option');
                noOpt.value = '';
                noOpt.textContent = 'Không có voucher khả dụng';
                select.appendChild(noOpt);
            }
        })
        .catch(err => {
            const errOpt = document.createElement('option');
            errOpt.value = '';
            errOpt.textContent = 'Lỗi tải voucher';
            select.appendChild(errOpt);
            console.error(err);
        });
    // Khi thay đổi lựa chọn, áp dụng voucher
    select.addEventListener('change', async () => {
        const code = select.value;
        if (!code) {
            // Không dùng voucher
            appliedVoucherCode = '';
            discountAmount = 0;
            document.getElementById('discount-info').textContent = '';
            updateTotals();
            return;
        }
        try {
            const res = await fetch('/api/check_voucher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();
            if (data.error) {
                appliedVoucherCode = '';
                discountAmount = 0;
                document.getElementById('discount-info').textContent = data.error;
            } else {
                appliedVoucherCode = data.code;
                document.getElementById('discount-info').textContent = 'Đã áp dụng voucher ' + data.code;
                updateTotals();
            }
        } catch (err) {
            showNotification('Không thể kiểm tra voucher', 'error');
        }
    });
}

// Khởi tạo lựa chọn phương thức thanh toán (COD hoặc chuyển khoản)
function initPaymentMethod() {
    const container = document.getElementById('payment-select-container');
    if (!container) return;
    container.innerHTML = '';
    // Tiêu đề
    const title = document.createElement('p');
    title.textContent = 'Chọn phương thức thanh toán:';
    title.style.marginBottom = '8px';
    container.appendChild(title);
    // Các tùy chọn
    const options = [
        { value: 'cod', label: 'Thanh toán khi nhận hàng' },
        { value: 'bank', label: 'Thanh toán qua ngân hàng', disabled: true, notice: 'Đang bảo trì' }
    ];
    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'payment-options';
    options.forEach(opt => {
        const labelEl = document.createElement('label');
        labelEl.className = 'payment-option';
        if (opt.disabled) {
            labelEl.style.opacity = '0.5';
            labelEl.style.cursor = 'not-allowed';
            labelEl.style.position = 'relative';
        }
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'payment-method';
        radio.value = opt.value;
        if (opt.disabled) {
            radio.disabled = true;
        }
        // Check default - chỉ chọn COD nếu bank bị disabled
        if (opt.value === 'cod' && selectedPaymentMethod !== 'bank') {
            radio.checked = true;
            selectedPaymentMethod = 'cod';
        } else if (opt.value === selectedPaymentMethod && !opt.disabled) {
            radio.checked = true;
        }
        radio.addEventListener('change', () => {
            if (opt.value === 'bank') {
                // Hiện thông báo khi chọn bank
                showNotification('Đang bảo trì cổng thanh toán. Vui lòng chọn phương thức thanh toán khác.', 'error');
                // Tự động chuyển về COD
                selectedPaymentMethod = 'cod';
                const codRadio = document.querySelector('input[name="payment-method"][value="cod"]');
                if (codRadio) {
                    codRadio.checked = true;
                }
            } else {
                selectedPaymentMethod = opt.value;
            }
        });
        const span = document.createElement('span');
        span.textContent = opt.label;
        if (opt.notice) {
            const notice = document.createElement('span');
            notice.textContent = ' (' + opt.notice + ')';
            notice.style.color = '#e53935';
            notice.style.fontSize = '12px';
            notice.style.fontWeight = '600';
            span.appendChild(notice);
        }
        labelEl.appendChild(radio);
        labelEl.appendChild(span);
        optionsDiv.appendChild(labelEl);
    });
    container.appendChild(optionsDiv);
}

// Cập nhật tổng tiền và hiển thị discount/finalTotal
function updateTotals(subtotalOverride) {
    if (typeof subtotalOverride === 'number' && !isNaN(subtotalOverride)) {
        currentCartSubtotal = subtotalOverride;
    }
    let total = currentCartSubtotal;
    let final = total;
    if (appliedVoucherCode) {
        // Gọi API check_voucher để lấy type/value
        // Lấy ngay? Sử dụng fetch synchronous not recommended; we already have voucher data from last call but we didn't store discount_type/value; we compute by calling API again.
        fetch('/api/check_voucher.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: appliedVoucherCode })
        }).then(res => res.json()).then(data => {
            if (!data.error) {
                // Compute discount
                if (data.type === 'percent') {
                    discountAmount = total * (data.value / 100);
                } else {
                    discountAmount = data.value;
                }
                if (discountAmount > total) discountAmount = total;
                final = total - discountAmount;
                document.getElementById('final-total').textContent = formatCurrency(final);
                document.getElementById('final-total-area').style.display = 'block';
                document.getElementById('discount-info').textContent = 'Đã giảm: ' + formatCurrency(discountAmount);
            }
        }).catch(err => {
            console.error(err);
        });
    } else {
        document.getElementById('final-total-area').style.display = 'none';
        document.getElementById('discount-info').textContent = '';
    }
}

function buildPriceHtml(item, skipSavingText = false) {
    const price = Number(item.price || item.original_price || 0);
    const sale = item.sale_price !== null && item.sale_price !== undefined ? Number(item.sale_price) : null;
    const effective = Number(item.effective_price || item.price || item.original_price || 0);
    if (sale !== null && sale < price) {
        const saving = skipSavingText ? '' : `<p class="saving-text">Tiết kiệm ${formatCurrency(price - sale)}</p>`;
        return `
            <div class="price-stack">
                <span class="sale-price">${formatCurrency(sale)}</span>
                <span class="original-price">${formatCurrency(price)}</span>
                ${saving}
            </div>
        `;
    }
    return `<span class="sale-price">${formatCurrency(effective)}</span>`;
}

function buildStockWarning(item) {
    if (item.is_out_of_stock) {
        return `<p class="stock-warning">Hết hàng - vui lòng xóa sản phẩm khỏi giỏ</p>`;
    }
    if (item.needs_adjustment) {
        return `<p class="stock-warning">Chỉ còn ${item.stock} sản phẩm</p>`;
    }
    if (item.unit_saving > 0) {
        return `<p class="saving-pill">Tiết kiệm ${formatCurrency(item.unit_saving)} mỗi sản phẩm</p>`;
    }
    return '';
}

function formatCurrency(value) {
    return currencyFormatter.format(Number(value || 0));
}
// JS for cart page
// Biến toàn cục lưu phương thức thanh toán được chọn
let selectedPaymentMethod = 'cod';

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
            // Lấy địa chỉ được chọn: ưu tiên radio; fallback select
            let addressId = 0;
            const radioAddr = document.querySelector('input[name="address-option"]:checked');
            if (radioAddr) {
                addressId = parseInt(radioAddr.value);
            } else {
                const select = document.getElementById('select-address');
                addressId = select ? parseInt(select.value) : 0;
            }
            const voucherCode = appliedVoucherCode || '';
            const paymentMethod = selectedPaymentMethod || 'cod';
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
        const cartItems = data.cart || {};
        const savedItems = data.saved || {};
        const cartTable = document.getElementById('cart-items');
        const savedTable = document.getElementById('saved-items');
        let total = 0;
        cartTable.innerHTML = '';
        savedTable.innerHTML = '';
        // Render cart items
        Object.values(cartItems).forEach(item => {
            const row = document.createElement('tr');
            const subtotal = item.price * item.quantity;
            total += subtotal;
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${Number(item.price).toLocaleString()}₫</td>
                <td><input type="number" min="1" value="${item.quantity}" data-id="${item.product_id}" onchange="updateQuantity(this)" /></td>
                <td>${Number(subtotal).toLocaleString()}₫</td>
                <td>
                    <button onclick="saveForLater(${item.product_id})">Lưu</button>
                    <button onclick="removeItem(${item.product_id}, false)">Xóa</button>
                </td>
            `;
            cartTable.appendChild(row);
        });
        // Render saved items
        Object.values(savedItems).forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${Number(item.price).toLocaleString()}₫</td>
                <td>${item.quantity}</td>
                <td>
                    <button onclick="moveToCart(${item.product_id})">Mua ngay</button>
                    <button onclick="removeItem(${item.product_id}, true)">Xóa</button>
                </td>
            `;
            savedTable.appendChild(row);
        });
        document.getElementById('cart-total').textContent = Number(total).toLocaleString() + '₫';
        // Sau khi tính tổng, cập nhật tổng cuối cùng nếu có voucher
        updateTotals(total);
        // Update cart count in header
        const count = Object.keys(cartItems).length;
        const cartCount = document.getElementById('cart-count');
        if (cartCount) cartCount.textContent = count > 0 ? `(${count})` : '';
    } catch (err) {
        console.error(err);
    }
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
        if ((data && data.message) || data.adjusted) {
            showNotification(data.message || 'Đã cập nhật số lượng.', 'success');
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

// Load danh sách địa chỉ và hiển thị select
async function loadAddressOptions() {
    const container = document.getElementById('address-select-container');
    if (!container) return;
    container.innerHTML = '';
    try {
        const res = await fetch('/api/get_addresses.php');
        const addresses = await res.json();
        if (!Array.isArray(addresses) || addresses.length === 0) {
            container.innerHTML = '<p>Không có địa chỉ giao hàng. Vui lòng thêm trong Trang cá nhân.</p>';
            return;
        }
        // Tạo tiêu đề
        const title = document.createElement('p');
        title.textContent = 'Chọn địa chỉ giao hàng:';
        title.style.marginBottom = '8px';
        container.appendChild(title);
        // Tạo vùng chứa các tùy chọn địa chỉ
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'address-options';
        addresses.forEach((addr, idx) => {
            const labelEl = document.createElement('label');
            labelEl.className = 'address-option';
            // Radio input
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'address-option';
            radio.value = addr.id;
            // Chọn mặc định địa chỉ đầu tiên
            if (idx === 0) radio.checked = true;
            radio.addEventListener('change', () => {
                // Nothing extra needed here; selected value will be read on checkout
            });
            // Nội dung hiển thị địa chỉ
            const card = document.createElement('div');
            card.className = 'address-card';
            card.innerHTML = `<strong>${addr.recipient_name}</strong><br><span>${addr.address}</span>`;
            labelEl.appendChild(radio);
            labelEl.appendChild(card);
            optionsDiv.appendChild(labelEl);
        });
        container.appendChild(optionsDiv);
    } catch (err) {
        container.innerHTML = '<p>Không thể tải địa chỉ.</p>';
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
        { value: 'bank', label: 'Thanh toán qua ngân hàng' }
    ];
    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'payment-options';
    options.forEach(opt => {
        const labelEl = document.createElement('label');
        labelEl.className = 'payment-option';
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'payment-method';
        radio.value = opt.value;
        // Check default
        if (opt.value === selectedPaymentMethod) radio.checked = true;
        radio.addEventListener('change', () => {
            selectedPaymentMethod = opt.value;
        });
        const span = document.createElement('span');
        span.textContent = opt.label;
        labelEl.appendChild(radio);
        labelEl.appendChild(span);
        optionsDiv.appendChild(labelEl);
    });
    container.appendChild(optionsDiv);
}

// Cập nhật tổng tiền và hiển thị discount/finalTotal
function updateTotals(currentTotal) {
    // Nếu currentTotal truyền vào, đặt total hiện tại; nếu không, lấy từ #cart-total
    let total;
    if (typeof currentTotal === 'number' && !isNaN(currentTotal)) {
        total = currentTotal;
    } else {
        const text = document.getElementById('cart-total').textContent.replace(/₫|,/g, '');
        total = parseFloat(text);
    }
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
                document.getElementById('final-total').textContent = Number(final).toLocaleString() + '₫';
                document.getElementById('final-total-area').style.display = 'block';
                document.getElementById('discount-info').textContent = 'Đã giảm: ' + Number(discountAmount).toLocaleString() + '₫';
            }
        }).catch(err => {
            console.error(err);
        });
    } else {
        document.getElementById('final-total-area').style.display = 'none';
        document.getElementById('discount-info').textContent = '';
    }
}
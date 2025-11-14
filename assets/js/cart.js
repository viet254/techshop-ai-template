// JS for cart page
document.addEventListener('DOMContentLoaded', async () => {
    loadCart();
    // Load addresses for selection (if user logged in)
    loadAddressOptions();
    // Init voucher input
    initVoucher();
    // Xử lý thanh toán
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            // Lấy địa chỉ được chọn và mã voucher
            const select = document.getElementById('select-address');
            const addressId = select ? parseInt(select.value) : 0;
            const voucherCode = appliedVoucherCode || '';
            try {
                const res = await fetch('/techshop-ai-template/api/checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ address_id: addressId, voucher_code: voucherCode })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Đã đặt đơn hàng thành công! Mã đơn: ' + data.order_id + '\nThành tiền: ' + Number(data.final_total).toLocaleString() + '₫');
                    // Reset voucher & address selection
                    appliedVoucherCode = '';
                    discountAmount = 0;
                    loadCart();
                    loadAddressOptions();
                    updateTotals(0);
                } else {
                    alert(data.message || 'Thanh toán thất bại.');
                }
            } catch (err) {
                alert('Không thể thanh toán.');
            }
        });
    }
});

async function loadCart() {
    try {
        const res = await fetch('/techshop-ai-template/api/get_cart.php');
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
        await fetch('/techshop-ai-template/api/update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, quantity })
        });
        loadCart();
    } catch (err) {
        console.error(err);
    }
}

async function saveForLater(productId) {
    try {
        await fetch('/techshop-ai-template/api/save_for_later.php', {
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
        await fetch('/techshop-ai-template/api/move_to_cart.php', {
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
        await fetch('/techshop-ai-template/api/remove_item.php', {
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
        const res = await fetch('/techshop-ai-template/api/get_addresses.php');
        const addresses = await res.json();
        if (!Array.isArray(addresses) || addresses.length === 0) {
            container.innerHTML = '<p>Không có địa chỉ giao hàng. Vui lòng thêm trong Trang cá nhân.</p>';
            return;
        }
        const label = document.createElement('label');
        label.textContent = 'Chọn địa chỉ giao hàng: ';
        const select = document.createElement('select');
        select.id = 'select-address';
        addresses.forEach(addr => {
            const option = document.createElement('option');
            option.value = addr.id;
            option.textContent = `${addr.recipient_name} - ${addr.address}`;
            select.appendChild(option);
        });
        label.appendChild(select);
        container.appendChild(label);
    } catch (err) {
        container.innerHTML = '<p>Không thể tải địa chỉ.</p>';
    }
}

// Khởi tạo ô nhập voucher và nút áp dụng
function initVoucher() {
    const container = document.getElementById('voucher-container');
    if (!container) return;
    container.innerHTML = '';
    const label = document.createElement('label');
    label.textContent = 'Mã voucher: ';
    const input = document.createElement('input');
    input.type = 'text';
    input.id = 'voucher-input';
    const button = document.createElement('button');
    button.id = 'apply-voucher-btn';
    button.textContent = 'Áp dụng';
    label.appendChild(input);
    container.appendChild(label);
    container.appendChild(button);
    // Sự kiện áp dụng
    button.addEventListener('click', async () => {
        const code = input.value.trim();
        if (!code) {
            alert('Vui lòng nhập mã voucher');
            return;
        }
        try {
            const res = await fetch('/techshop-ai-template/api/check_voucher.php', {
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
                // Sẽ tính discountAmount ở updateTotals dựa trên total hiện tại
                document.getElementById('discount-info').textContent = 'Đã áp dụng voucher ' + data.code;
                updateTotals();
            }
        } catch (err) {
            alert('Không thể kiểm tra voucher');
        }
    });
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
        fetch('/techshop-ai-template/api/check_voucher.php', {
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
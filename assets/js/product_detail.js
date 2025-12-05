// JS for product detail page
document.addEventListener('DOMContentLoaded', async () => {
    const section = document.querySelector('.product-detail');
    if (!section) return;
    const productId = section.dataset.productId;
    if (!productId) return;
    // Load product details
    try {
        const res = await fetch(`/api/get_product_detail.php?id=${productId}`);
        const p = await res.json();
        // Đặt đường dẫn ảnh dựa trên thư mục assets để hiển thị đúng
        document.getElementById('prod-img').src = `assets/images/${p.image}`;
        document.getElementById('prod-name').textContent = p.name;
        document.getElementById('prod-desc').textContent = p.description;
        // Render thông số kỹ thuật nếu có
        const specsContainer = document.getElementById('prod-specs');
        if (p.specs && p.specs.trim() !== '') {
            const specsItems = p.specs.split(';').map(s => s.trim()).filter(s => s);
            let specsHtml = '<h4>Thông số kỹ thuật</h4><ul>';
            specsItems.forEach(item => {
                specsHtml += `<li>${item}</li>`;
            });
            specsHtml += '</ul>';
            specsContainer.innerHTML = specsHtml;
        } else {
            specsContainer.innerHTML = '';
        }
        document.getElementById('prod-stock').textContent = p.stock > 0 ? 'Còn hàng' : 'Hết hàng';
        document.getElementById('prod-cat').textContent = p.category;
        // Hiển thị giá và giá khuyến mãi (nếu có)
        const priceEl = document.getElementById('prod-price');
        const original = Number(p.price);
        const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
        if (sale !== null) {
            priceEl.innerHTML = `<span class="sale-price">${sale.toLocaleString()}₫</span><span class="original-price">${original.toLocaleString()}₫</span>`;
        } else {
            priceEl.textContent = original.toLocaleString() + '₫';
        }
        const qtyInput = document.getElementById('qty');
        if (qtyInput) {
            qtyInput.setAttribute('max', p.stock);
            // Đảm bảo giá trị hiện tại không vượt quá tồn kho hoặc nhỏ hơn 1
            let currentQty = parseInt(qtyInput.value) || 1;
            if (currentQty > p.stock) currentQty = p.stock;
            if (currentQty < 1) currentQty = 1;
            qtyInput.value = currentQty;
            qtyInput.addEventListener('input', function() {
                let val = parseInt(this.value);
                if (isNaN(val) || val < 1) val = 1;
                if (val > p.stock) val = p.stock;
                this.value = val;
            });
        }
        // After successfully loading product details, fetch related products
        loadRelatedProducts(productId);
    } catch (err) {
        console.error(err);
    }
    // Add to cart
    document.getElementById('add-cart-btn').addEventListener('click', async () => {
        const qty = parseInt(document.getElementById('qty').value);
        try {
            const resp = await fetch('/api/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: productId, quantity: qty })
            });
            const data = await resp.json();
            if (data.success) {
                showNotification(data.message || 'Đã thêm vào giỏ hàng!', 'success');
            } else {
                showNotification(data.message || 'Không thể thêm vào giỏ hàng.', 'error');
            }
        } catch (err) {
            showNotification('Không thể thêm vào giỏ hàng.', 'error');
        }
    });
    // Buy now: thêm vào giỏ sau đó chuyển tới trang giỏ hàng để thanh toán
    const buyNowBtn = document.getElementById('buy-now-btn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', async () => {
            const qty = parseInt(document.getElementById('qty').value);
            try {
                const resp = await fetch('/api/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: productId, quantity: qty })
                });
                const data = await resp.json();
                if (data.success) {
                    // Show success message then redirect to cart page
                    showNotification('Đã thêm vào giỏ hàng.', 'success');
                    setTimeout(() => {
                        window.location.href = '/cart.php';
                    }, 1500);
                } else {
                    showNotification(data.message || 'Không thể mua ngay.', 'error');
                }
            } catch (err) {
                showNotification('Không thể mua ngay.', 'error');
            }
        });
    }
    // Load reviews
    loadReviews(productId);
    // Submit review
    document.getElementById('submit-review').addEventListener('click', async () => {
        const rating = document.getElementById('rating').value;
        // Allow users to submit without entering a text comment; treat empty as empty string
        const comment = document.getElementById('comment').value.trim();
        try {
            const res = await fetch('/api/add_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, rating, comment })
            });
            const data = await res.json();
            // Clear the comment textarea regardless of success
            document.getElementById('comment').value = '';
            // Reload reviews to reflect the new entry
            loadReviews(productId);
        } catch (err) {
            showNotification('Không thể gửi đánh giá.', 'error');
        }
    });
    
    // Function to load related products
    async function loadRelatedProducts(id) {
        try {
            const res = await fetch(`/api/get_related_products.php?product_id=${id}`);
            const products = await res.json();
            const container = document.getElementById('related-products');
            if (!container) return;
            container.innerHTML = '';
            if (!products || products.length === 0) {
                container.innerHTML = '<p>Không có sản phẩm liên quan.</p>';
                return;
            }
            products.forEach(prod => {
                const card = document.createElement('div');
                card.className = 'related-item';
                card.innerHTML = `
                    <a href="product_detail.php?id=${prod.id}">
                        <img src="assets/images/${prod.image}" alt="${prod.name}" />
                        <h4>${prod.name}</h4>
                        <p>${Number(prod.price).toLocaleString()}₫</p>
                    </a>
                `;
                container.appendChild(card);
            });
        } catch (err) {
            console.error(err);
        }
    }
});

async function loadReviews(id) {
    try {
        const res = await fetch(`/api/get_reviews.php?product_id=${id}`);
        const reviews = await res.json();
        const list = document.getElementById('comment-list');
        list.innerHTML = '';
        if (reviews.length === 0) {
            document.getElementById('average-rating').innerHTML = 'Chưa có đánh giá.';
            return;
        }
        const avg = reviews.reduce((a, b) => a + parseInt(b.rating), 0) / reviews.length;
        document.getElementById('average-rating').innerHTML = `Điểm trung bình: ${avg.toFixed(1)}⭐ (${reviews.length} lượt)`;
        reviews.forEach(r => {
            const div = document.createElement('div');
            div.className = 'comment';
            div.innerHTML = `<strong>${r.username}</strong> - ${'⭐'.repeat(r.rating)}<p>${r.comment}</p>`;
            list.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
}
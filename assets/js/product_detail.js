// JS for product detail page
document.addEventListener('DOMContentLoaded', async () => {
    const section = document.querySelector('.product-detail');
    if (!section) return;
    const productId = section.dataset.productId;
    if (!productId) return;
    // Load product details
    try {
        const res = await fetch(`/techshop-ai-template/api/get_product_detail.php?id=${productId}`);
        const p = await res.json();
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
        document.getElementById('prod-price').textContent = Number(p.price).toLocaleString() + '₫';
    } catch (err) {
        console.error(err);
    }
    // Add to cart
    document.getElementById('add-cart-btn').addEventListener('click', async () => {
        const qty = parseInt(document.getElementById('qty').value);
        try {
            const resp = await fetch('/techshop-ai-template/api/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: productId, quantity: qty })
            });
            const data = await resp.json();
            alert('Đã thêm vào giỏ hàng!');
        } catch (err) {
            alert('Không thể thêm vào giỏ hàng.');
        }
    });
    // Buy now: thêm vào giỏ sau đó chuyển tới trang giỏ hàng để thanh toán
    const buyNowBtn = document.getElementById('buy-now-btn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', async () => {
            const qty = parseInt(document.getElementById('qty').value);
            try {
                const resp = await fetch('/techshop-ai-template/api/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: productId, quantity: qty })
                });
                const data = await resp.json();
                // Sau khi thêm, chuyển người dùng tới trang giỏ hàng để thanh toán
                window.location.href = '/techshop-ai-template/cart.php';
            } catch (err) {
                alert('Không thể mua ngay.');
            }
        });
    }
    // Load reviews
    loadReviews(productId);
    // Submit review
    document.getElementById('submit-review').addEventListener('click', async () => {
        const rating = document.getElementById('rating').value;
        const comment = document.getElementById('comment').value.trim();
        if (!comment) return;
        try {
            const res = await fetch('/techshop-ai-template/api/add_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, rating, comment })
            });
            const data = await res.json();
            document.getElementById('comment').value = '';
            loadReviews(productId);
        } catch (err) {
            alert('Không thể gửi đánh giá.');
        }
    });
});

async function loadReviews(id) {
    try {
        const res = await fetch(`/techshop-ai-template/api/get_reviews.php?product_id=${id}`);
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
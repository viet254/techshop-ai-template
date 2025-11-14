// JavaScript to load product list from API
document.addEventListener('DOMContentLoaded', async () => {
    const listContainer = document.getElementById('productList');
    if (!listContainer) return;
    try {
        // Đọc tham số tìm kiếm trên URL
    const params = new URLSearchParams(window.location.search);
    const q = params.get('q') || '';
    const cat = params.get('cat') || '';
    let url = '/techshop-ai-template/api/get_products.php';
    // Ưu tiên tìm kiếm q, nếu có thì bỏ qua cat; nếu không, dùng cat
    if (q) {
        url += '?q=' + encodeURIComponent(q);
    } else if (cat) {
        url += '?cat=' + encodeURIComponent(cat);
    }
        const res = await fetch(url);
        const products = await res.json();
        if (products.length === 0) {
            listContainer.innerHTML = '<p>Không tìm thấy sản phẩm.</p>';
            return;
        }
        listContainer.innerHTML = products.map(p => `
            <div class="product-card">
                <img src="assets/images/${p.image}" alt="${p.name}" />
                <h4>${p.name}</h4>
                <p>${Number(p.price).toLocaleString()}₫</p>
                <button onclick="location.href='product_detail.php?id=${p.id}'">Xem chi tiết</button>
            </div>
        `).join('');
    } catch (err) {
        listContainer.innerHTML = '<p>Không thể tải danh sách sản phẩm.</p>';
    }
});
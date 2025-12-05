// JavaScript to load product list from API

document.addEventListener('DOMContentLoaded', async () => {
    const listContainer = document.getElementById('productList');
    if (!listContainer) return;

    function parseLaptopSpecs(specStr) {
        const specs = {
            core: '',
            ram: '',
            storage: '',
            card: '',
            screen: ''
        };
        if (!specStr) return specs;

        const parts = specStr.split(';');
        parts.forEach(raw => {
            const part = raw.trim();
            if (!part) return;
            const lower = part.toLowerCase();
            const value = part.split(':').slice(1).join(':').trim() || part;

            if (lower.includes('cpu') || lower.includes('core')) {
                specs.core = value;
            } else if (lower.includes('ram')) {
                specs.ram = value;
            } else if (lower.includes('ssd') || lower.includes('hdd') || lower.includes('nvme') || lower.includes('ổ cứng') || lower.includes('storage')) {
                specs.storage = value;
            } else if (lower.includes('gpu') || lower.includes('vga') || lower.includes('card')) {
                specs.card = value;
            } else if (lower.includes('màn') || lower.includes('inch') || lower.includes('"')) {
                specs.screen = value;
            }
        });
        return specs;
    }

    try {
        const params = new URLSearchParams(window.location.search);
        const q = params.get('q') || '';
        const cat = params.get('cat') || '';

        let url = 'api/get_products.php';
        if (q) {
            url += '?q=' + encodeURIComponent(q);
        } else if (cat) {
            url += '?cat=' + encodeURIComponent(cat);
        }

        const res = await fetch(url);
        const products = await res.json();
        if (!Array.isArray(products) || products.length === 0) {
            listContainer.innerHTML = '<p>Không tìm thấy sản phẩm.</p>';
            return;
        }

        listContainer.innerHTML = products.map(p => {
            const isLaptop = (p.category || '').toLowerCase().includes('laptop');
            const original = Number(p.price);
            const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
            const hasSale = sale !== null;
            const finalPrice = hasSale ? sale : original;
            const discountPercent = hasSale ? Math.round((original - sale) / original * 100) : null;
            const priceHtml = hasSale
                ? `<div class="price-container"><span class="sale-price">${finalPrice.toLocaleString()}₫</span><span class="original-price">${original.toLocaleString()}₫</span></div>`
                : `<div class="price-container"><span class="sale-price">${original.toLocaleString()}₫</span></div>`;
            // Chuẩn bị nội dung giá cho nút bấm: nếu có giảm giá sẽ hiển thị cả giá khuyến mãi và giá gốc, ngược lại chỉ hiển thị giá
            const buttonPriceHtml = hasSale
                ? `<span class="sale-price">${finalPrice.toLocaleString()}₫</span><span class="original-price">${original.toLocaleString()}₫</span>`
                : `<span class="sale-price">${original.toLocaleString()}₫</span>`;
            const discountBadge = hasSale ? `<div class="discount-badge">Giảm ${discountPercent}%</div>` : '';

            if (isLaptop) {
                const specObj = parseLaptopSpecs(p.specs || '');
                return `
                <div class="product-card laptop-card">
                    ${discountBadge}
                    <img src="assets/images/${p.image}" alt="${p.name}" />
                    <h4>${p.name}</h4>
                    <div class="laptop-specs">
                        <div class="laptop-spec-row"><span class="label">Core</span><span class="value">${specObj.core || '-'}</span></div>
                        <div class="laptop-spec-row"><span class="label">RAM</span><span class="value">${specObj.ram || '-'}</span></div>
                        <div class="laptop-spec-row"><span class="label">Ổ cứng</span><span class="value">${specObj.storage || '-'}</span></div>
                        <div class="laptop-spec-row"><span class="label">Card</span><span class="value">${specObj.card || '-'}</span></div>
                        <div class="laptop-spec-row"><span class="label">M.hình</span><span class="value">${specObj.screen || '-'}</span></div>
                    </div>
                    <!-- Giá được hiển thị trong nút để bao gồm cả giá gốc và giá giảm nếu có -->
                    <button class="laptop-price-btn" onclick="location.href='product_detail.php?id=${p.id}'">${buttonPriceHtml}</button>
                </div>
                `;
            }

            // Layout cho các loại sản phẩm khác
            const desc = p.description || '';
            const shortDesc = desc.length > 120 ? desc.substring(0, 120) + '...' : desc;
            return `
            <div class="product-card">
                ${discountBadge}
                <img src="assets/images/${p.image}" alt="${p.name}" />
                <h4>${p.name}</h4>
                <p class="product-short-desc">${shortDesc}</p>
                <!-- Giá được hiển thị trong nút để bao gồm cả giá gốc và giá giảm nếu có -->
                <button class="product-price-btn" onclick="location.href='product_detail.php?id=${p.id}'">${buttonPriceHtml}</button>
            </div>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        listContainer.innerHTML = '<p>Không thể tải danh sách sản phẩm.</p>';
    }
});

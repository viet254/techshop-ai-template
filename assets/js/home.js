// JavaScript to load featured categories on the home page

document.addEventListener('DOMContentLoaded', async () => {
    const laptopContainer = document.getElementById('home-laptop');
    const linhkienContainer = document.getElementById('home-linhkien');
    if (!laptopContainer || !linhkienContainer) return;

    // Hàm tách thông số laptop thành Core / RAM / Ổ cứng / Card / M.hình
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
        // ---------- Laptops ----------
        const resLaptop = await fetch('api/get_products.php?cat=Laptop');
        let laptops = await resLaptop.json();
        laptops.sort((a, b) => b.id - a.id);
        laptops = laptops.slice(0, 8);

        laptopContainer.innerHTML = laptops.map(p => {
            const specObj = parseLaptopSpecs(p.specs || '');
            // Tính giá hiển thị và khuyến mãi nếu có
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
        }).join('');

        // ---------- Linh kiện (giữ layout cũ) ----------
        const resLinhkien = await fetch('api/get_products.php?cat=' + encodeURIComponent('Linh kiện'));
        let linhkiens = await resLinhkien.json();
        linhkiens.sort((a, b) => b.id - a.id);
        linhkiens = linhkiens.slice(0, 8);
        linhkienContainer.innerHTML = linhkiens.map(p => {
            const desc = p.description || '';
            const shortDesc = desc.length > 120 ? desc.substring(0, 120) + '...' : desc;
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

        // ---------- Giảm giá sâu ----------
        const discountContainer = document.getElementById('home-discount');
        if (discountContainer) {
            try {
                // Lấy tất cả sản phẩm và lọc ra những sản phẩm có sale_price nhỏ hơn giá gốc
                const resAll = await fetch('api/get_products.php');
                let allProducts = await resAll.json();
                // Chuyển đổi kiểu dữ liệu
                allProducts = Array.isArray(allProducts) ? allProducts : [];
                const discounted = allProducts.filter(p => p.sale_price && p.sale_price < p.price);
                // Tính phần trăm giảm giá để sắp xếp
                discounted.sort((a, b) => {
                    const aOrig = Number(a.price);
                    const aSale = Number(a.sale_price);
                    const bOrig = Number(b.price);
                    const bSale = Number(b.sale_price);
                    const aDisc = aOrig > 0 ? (aOrig - aSale) / aOrig : 0;
                    const bDisc = bOrig > 0 ? (bOrig - bSale) / bOrig : 0;
                    return bDisc - aDisc;
                });
                // Lấy tối đa 8 sản phẩm giảm giá cao nhất
                const topDiscounts = discounted.slice(0, 8);
                discountContainer.innerHTML = topDiscounts.map(p => {
                    const original = Number(p.price);
                    const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
                    const hasSale = sale !== null;
                    const finalPrice = hasSale ? sale : original;
                    const discountPercent = hasSale ? Math.round((original - sale) / original * 100) : null;
                    // Nội dung giá cho nút bấm
                    const buttonPriceHtml = hasSale
                        ? `<span class="sale-price">${finalPrice.toLocaleString()}₫</span><span class="original-price">${original.toLocaleString()}₫</span>`
                        : `<span class="sale-price">${original.toLocaleString()}₫</span>`;
                    const discountBadge = hasSale ? `<div class="discount-badge">Giảm ${discountPercent}%</div>` : '';
                    const desc = p.description || '';
                    const shortDesc = desc.length > 120 ? desc.substring(0, 120) + '...' : desc;
                    return `
                    <div class="product-card">
                        ${discountBadge}
                        <img src="assets/images/${p.image}" alt="${p.name}" />
                        <h4>${p.name}</h4>
                        <p class="product-short-desc">${shortDesc}</p>
                        <button class="product-price-btn" onclick="location.href='product_detail.php?id=${p.id}'">${buttonPriceHtml}</button>
                    </div>
                    `;
                }).join('');
            } catch (error) {
                console.error(error);
            }
        }
    } catch (err) {
        console.error(err);
    }

    // Điều chỉnh chiều cao quảng cáo theo chiều cao của menu danh mục
    function adjustAdHeight() {
        const nav = document.querySelector('.home-page .categories-nav');
        const leftImg = document.querySelector('.ads-left img');
        const rightImg = document.querySelector('.ads-right img');
        if (nav && leftImg && rightImg) {
            const navHeight = nav.offsetHeight;
            if (navHeight > 0) {
                leftImg.style.height = navHeight + 'px';
                rightImg.style.height = navHeight + 'px';
            }
        }
    }

    adjustAdHeight();
    window.addEventListener('resize', adjustAdHeight);
});

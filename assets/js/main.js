// JavaScript to load product list from API

document.addEventListener('DOMContentLoaded', async () => {
    const listContainer = document.getElementById('productList');
    if (!listContainer) return;

    const paginationEl = document.getElementById('pagination');
    const sortSelect = document.getElementById('sortSelect');
    const toggleAdvancedBtn = document.getElementById('toggleAdvancedFilters');
    const advancedFilterModal = document.getElementById('advancedFilterModal');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const categorySelect = document.getElementById('filterCategory');
    const keywordInput = document.getElementById('keyword');
    const priceRangePresetSelect = document.getElementById('priceRangePreset');
    const onSaleCheckbox = document.getElementById('onSaleOnly');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');

    const itemsPerPage = 16;
    let currentPage = 1;
    let currentSort = 'default';
    let currentFilters = {
        minPrice: null,
        maxPrice: null,
        category: '',
        keyword: '',
        onSale: false
    };

    let allProducts = [];
    let filteredProducts = [];

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

    function getFinalPrice(p) {
        const original = Number(p.price);
        const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
        return sale !== null ? sale : original;
    }

    function getDiscountPercent(p) {
        const original = Number(p.price);
        const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
        if (sale === null || original === 0) return 0;
        return Math.round((original - sale) / original * 100);
    }

    function applyFilters(list) {
        return list.filter(p => {
            const price = getFinalPrice(p);
            if (currentFilters.minPrice !== null && price < currentFilters.minPrice) {
                return false;
            }
            if (currentFilters.maxPrice !== null && price > currentFilters.maxPrice) {
                return false;
            }
            if (currentFilters.category && (p.category || '') !== currentFilters.category) {
                return false;
            }
            if (currentFilters.onSale) {
                const sale = p.sale_price && p.sale_price < p.price;
                if (!sale) return false;
            }
            if (currentFilters.keyword) {
                const kw = currentFilters.keyword;
                const combined =
                    ((p.name || '') + ' ' + (p.description || '') + ' ' + (p.specs || '')).toLowerCase();
                if (!combined.includes(kw)) return false;
            }
            return true;
        });
    }

    function buildProductHtml(p) {
        const isLaptop = (p.category || '').toLowerCase().includes('laptop');
        const original = Number(p.price);
        const sale = p.sale_price && p.sale_price < p.price ? Number(p.sale_price) : null;
        const hasSale = sale !== null;
        const finalPrice = hasSale ? sale : original;
        const discountPercent = hasSale ? Math.round((original - sale) / original * 100) : null;
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
                    <button class="laptop-price-btn" onclick="location.href='product_detail.php?id=${p.id}'">${buttonPriceHtml}</button>
                </div>
            `;
        }
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
    }

    function renderProducts(page) {
        currentPage = page;
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const slice = filteredProducts.slice(start, end);
        listContainer.innerHTML = slice.map(buildProductHtml).join('');
    }

    function renderPagination() {
        if (!paginationEl) return;
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? ' active' : '';
            html += `<button class="page-btn${activeClass}" data-page="${i}">${i}</button>`;
        }
        html += `<button class="page-btn next-btn" data-page="next">&rarr;</button>`;
        paginationEl.innerHTML = html;
    }

    function sortProducts(list) {
        const cloned = [...list];
        switch (currentSort) {
            case 'price-asc':
                cloned.sort((a, b) => getFinalPrice(a) - getFinalPrice(b));
                break;
            case 'price-desc':
                cloned.sort((a, b) => getFinalPrice(b) - getFinalPrice(a));
                break;
            case 'name-asc':
                cloned.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                break;
            case 'discount-desc':
                cloned.sort((a, b) => getDiscountPercent(b) - getDiscountPercent(a));
                break;
            default:
                break;
        }
        return cloned;
    }

    function applyAndRender(page = 1) {
        const base = applyFilters(allProducts);
        filteredProducts = sortProducts(base);
        if (filteredProducts.length === 0) {
            listContainer.innerHTML = '<p>Không tìm thấy sản phẩm phù hợp.</p>';
            if (paginationEl) paginationEl.innerHTML = '';
            return;
        }
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
        const safePage = Math.max(1, Math.min(page, totalPages));
        renderProducts(safePage);
        renderPagination();
    }

    function closeDropdowns() {}

    try {
        const params = new URLSearchParams(window.location.search);
        const q = params.get('q') || '';
        const cat = params.get('cat') ? params.get('cat') : '';

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
            if (paginationEl) paginationEl.innerHTML = '';
            return;
        }

        allProducts = products;
        filteredProducts = [...allProducts];

        // Khởi tạo danh sách danh mục cho bộ lọc nâng cao
        if (categorySelect) {
            const categories = Array.from(
                new Set(
                    allProducts
                        .map(p => p.category || '')
                        .filter(Boolean)
                )
            );
            categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                categorySelect.appendChild(opt);
            });
        }

        applyAndRender(1);
    } catch (err) {
        console.error(err);
        listContainer.innerHTML = '<p>Không thể tải danh sách sản phẩm.</p>';
    }

    if (paginationEl) {
        paginationEl.addEventListener('click', function (e) {
            const target = e.target;
            if (!target.classList.contains('page-btn')) return;
            const pageAttr = target.getAttribute('data-page');
            const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
            if (pageAttr === 'next') {
                if (currentPage < totalPages) {
                    renderProducts(currentPage + 1);
                    renderPagination();
                }
            } else {
                const pageNum = parseInt(pageAttr, 10);
                if (!isNaN(pageNum) && pageNum >= 1 && pageNum <= totalPages) {
                    renderProducts(pageNum);
                    renderPagination();
                }
            }
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            currentSort = sortSelect.value;
            applyAndRender(1);
        });
    }

    // Sự kiện cho bộ lọc nâng cao (mở modal giữa màn hình)
    if (toggleAdvancedBtn && advancedFilterModal) {
        toggleAdvancedBtn.addEventListener('click', () => {
            advancedFilterModal.classList.remove('hidden');
        });
    }

    function updateFilterStateFromInputs() {
        let minVal = minPriceInput && minPriceInput.value ? Number(minPriceInput.value) : null;
        let maxVal = maxPriceInput && maxPriceInput.value ? Number(maxPriceInput.value) : null;

        // Ưu tiên khoảng giá nhanh nếu được chọn
        if (priceRangePresetSelect && priceRangePresetSelect.value) {
            const val = priceRangePresetSelect.value;
            const [minStr, maxStr] = val.split('-');
            const presetMin = minStr ? Number(minStr) : null;
            const presetMax = maxStr ? Number(maxStr) : null;
            if (!isNaN(presetMin)) minVal = presetMin;
            if (!isNaN(presetMax)) maxVal = presetMax;
        }

        currentFilters.minPrice = !isNaN(minVal) && minVal !== null ? minVal : null;
        currentFilters.maxPrice = !isNaN(maxVal) && maxVal !== null ? maxVal : null;
        currentFilters.category = categorySelect ? categorySelect.value : '';
        currentFilters.keyword = keywordInput && keywordInput.value
            ? keywordInput.value.trim().toLowerCase()
            : '';
        currentFilters.onSale = !!(onSaleCheckbox && onSaleCheckbox.checked);
    }

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            updateFilterStateFromInputs();
            applyAndRender(1);
            if (advancedFilterModal) {
                advancedFilterModal.classList.add('hidden');
            }
        });
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (minPriceInput) minPriceInput.value = '';
            if (maxPriceInput) maxPriceInput.value = '';
            if (categorySelect) categorySelect.value = '';
            if (keywordInput) keywordInput.value = '';
            if (priceRangePresetSelect) priceRangePresetSelect.value = '';
            if (onSaleCheckbox) onSaleCheckbox.checked = false;
            currentFilters = {
                minPrice: null,
                maxPrice: null,
                category: '',
                keyword: '',
                onSale: false
            };
            applyAndRender(1);
        });
    }

    // Đóng modal khi bấm nút Đóng hoặc click ra ngoài
    const closeFilterModalBtn = document.getElementById('closeFilterModal');
    if (closeFilterModalBtn && advancedFilterModal) {
        closeFilterModalBtn.addEventListener('click', () => {
            advancedFilterModal.classList.add('hidden');
        });
    }
    if (advancedFilterModal) {
        advancedFilterModal.addEventListener('click', (e) => {
            if (e.target === advancedFilterModal) {
                advancedFilterModal.classList.add('hidden');
            }
        });
    }
});

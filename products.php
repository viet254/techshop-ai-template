<?php
// Trang danh sách sản phẩm
include __DIR__ . '/includes/header.php';
?>
<main class="main-content products-page">
    <nav class="categories-nav">
        <ul>
            <li><a href="products.php?cat=Phụ kiện">Chuột - Bàn phím - Tai nghe</a></li>
            <li><a href="products.php?cat=Laptop">Laptop</a></li>
            <li><a href="products.php?cat=Linh kiện">Linh kiện</a></li>
            <li><a href="products.php?cat=Màn hình - Loa">Màn hình - Loa</a></li>
            <li><a href="products.php?cat=SSD">SSD</a></li>
            <li><a href="products.php?cat=RAM">RAM</a></li>
            <li><a href="products.php?cat=Thẻ nhớ">Thẻ nhớ</a></li>
            <li><a href="products.php?cat=USB">USB</a></li>
            <li><a href="products.php?cat=HDD">HDD</a></li>
        </ul>
    </nav>

    <section class="products-section">
        <h2>Danh sách sản phẩm</h2>
        <div class="products-section-inner">
            <div class="products-toolbar simple">
                <button id="toggleAdvancedFilters" class="filter-toggle-btn" type="button">
                    Bộ lọc nâng cao
                </button>

                <span class="toolbar-label">Sắp xếp:</span>
                <select id="sortSelect" class="sort-select">
                    <option value="default">Mặc định</option>
                    <option value="price-asc">Giá thấp đến cao</option>
                    <option value="price-desc">Giá cao đến thấp</option>
                    <option value="name-asc">Tên A → Z</option>
                    <option value="discount-desc">Ưu đãi nhiều</option>
                </select>
            </div>

            <div class="product-grid" id="productList">
                <!-- Sản phẩm sẽ được kết xuất bằng JavaScript -->
            </div>
            <!--
                Thêm thanh phân trang ở dưới danh sách sản phẩm.  
                Phần tử này được JavaScript sử dụng để hiển thị số trang và điều hướng.  
                Nằm bên trong khu vực products-section để giữ khoảng cách hợp lý so với lưới sản phẩm.
            -->
            <div id="pagination" class="pagination"></div>
        </div>
    </section>
    
    <!-- Modal bộ lọc nâng cao -->
    <div id="advancedFilterModal" class="modal hidden advanced-filter-modal">
        <div class="modal-content">
            <h3 class="filter-modal-title">Bộ lọc nâng cao</h3>
            <div class="advanced-filters" id="advancedFilters">
                <div class="filter-group">
                    <label for="keyword">Từ khóa</label>
                    <input type="text" id="keyword" placeholder="Tên sản phẩm, cấu hình, mô tả..." />
                </div>
                <div class="filter-group">
                    <label for="priceRangePreset">Khoảng giá</label>
                    <select id="priceRangePreset">
                        <option value="">Tất cả</option>
                        <option value="0-5000000">Dưới 5 triệu</option>
                        <option value="5000000-10000000">5 - 10 triệu</option>
                        <option value="10000000-20000000">10 - 20 triệu</option>
                        <option value="20000000-">Trên 20 triệu</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="minPrice">Giá từ (tùy chọn)</label>
                    <input type="number" id="minPrice" placeholder="0" min="0" />
                </div>
                <div class="filter-group">
                    <label for="maxPrice">Đến (tùy chọn)</label>
                    <input type="number" id="maxPrice" placeholder="50.000.000" min="0" />
                </div>
                <div class="filter-group">
                    <label for="filterCategory">Danh mục</label>
                    <select id="filterCategory">
                        <option value="">Tất cả</option>
                    </select>
                </div>
                <div class="filter-group checkbox-group">
                    <label>
                        <input type="checkbox" id="onSaleOnly" />
                        Chỉ hiển thị sản phẩm đang giảm giá
                    </label>
                </div>
            </div>
            <div class="filter-modal-actions">
                <button id="applyFilters" type="button">Tìm kiếm</button>
                <button id="clearFilters" type="button" class="secondary">Xóa</button>
                <button id="closeFilterModal" type="button" class="link-btn">Đóng</button>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/main.js" defer></script>

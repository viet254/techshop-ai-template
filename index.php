<?php
// Trang chủ TechShop AI
include __DIR__ . '/includes/header.php';
?>

<main class="main-content home-page">
    <!-- Menu danh mục dọc -->
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
    <!-- Nội dung chính -->
    <section class="content">
        <!-- Hero banner giống e-store -->
        <div class="hero-banner">
            <div class="hero-main">
                <span class="hero-badge">Ưu đãi laptop & linh kiện</span>
                <h1>TechShop AI - Laptop & Linh kiện chính hãng</h1>
                <p>
                    Nâng cấp hiệu năng học tập, làm việc và chơi game với laptop, RAM, SSD,
                    linh kiện mới nhất. Giao diện mới, trải nghiệm như một e-store hiện đại.
                </p>
                <div class="hero-actions">
                    <a href="products.php?cat=Laptop" class="btn-primary">Xem laptop</a>
                    <a href="products.php" class="btn-outline">Tất cả sản phẩm</a>
                </div>
            </div>
            <div class="hero-promos">
                <a href="products.php?cat=Laptop" class="hero-promo">
                    <img src="assets/images/banner-laptop-flashsale.png" alt="Flash sale laptop" />
                    <div class="hero-promo-text">
                        <h3>Flash Sale Laptop</h3>
                        <p>Giảm giá cực sốc</p>
                    </div>
                </a>
                <a href="products.php?cat=Linh kiện" class="hero-promo">
                    <img src="assets/images/banner-ram-ssd.png" alt="RAM & SSD" />
                    <div class="hero-promo-text">
                        <h3>RAM & SSD tốc độ cao</h3>
                        <p>Tăng tốc máy tính</p>
                    </div>
                </a>
                <a href="products.php?cat=Màn hình - Loa" class="hero-promo">
                    <img src="assets/images/banner-monitor-144hz.png" alt="Màn hình 144Hz" />
                    <div class="hero-promo-text">
                        <h3>Màn hình 144Hz</h3>
                        <p>Game mượt mà</p>
                    </div>
                </a>
                <a href="products.php?cat=Phụ kiện" class="hero-promo">
                    <img src="assets/images/banner-phu-kien.png" alt="Phụ kiện công nghệ" />
                    <div class="hero-promo-text">
                        <h3>Phụ kiện công nghệ</h3>
                        <p>Giá tốt mỗi ngày</p>
                    </div>
                </a>
            </div>
        </div>
        <!-- Danh sách sản phẩm nổi bật -->
        <div class="home-section">
            <h3>Laptop nổi bật</h3>
            <div id="home-laptop" class="home-products"></div>
        </div>
        <div class="home-section">
            <h3>Linh kiện nổi bật</h3>
            <div id="home-linhkien" class="home-products"></div>
        </div>
        <!-- Mục giảm giá sâu: hiển thị các sản phẩm có mức giảm giá lớn -->
        <div class="home-section">
            <h3>Giảm giá sâu</h3>
            <div id="home-discount" class="home-products"></div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/home.js" defer></script>

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
        <div class="product-grid" id="productList">
            <!-- Sản phẩm -->
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/main.js" defer></script>

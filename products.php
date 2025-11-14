<?php
// Trang danh sách sản phẩm
include __DIR__ . '/includes/header.php';
// Cho phép xem sản phẩm mà không cần đăng nhập
?>
<main class="main-content">
    <h2>Danh sách sản phẩm</h2>
    <div class="product-grid" id="productList">
        <!-- Sản phẩm sẽ được tải bằng JS -->
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/main.js" defer></script>
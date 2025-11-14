<?php
// Trang chi tiết sản phẩm
include __DIR__ . '/includes/header.php';

// Lấy ID sản phẩm từ query string
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<main class="main-content">
    <section class="product-detail" data-product-id="<?= $productId ?>">
        <div class="product-image">
            <img id="prod-img" src="" alt="Ảnh sản phẩm" />
        </div>
        <div class="product-info">
            <h2 id="prod-name">Đang tải...</h2>
            <p id="prod-desc"></p>
        <!-- Thông số kỹ thuật -->
        <div id="prod-specs" class="product-specs"></div>
            <div class="product-meta">
                <p><strong>Tình trạng:</strong> <span id="prod-stock"></span></p>
                <p><strong>Danh mục:</strong> <span id="prod-cat"></span></p>
            </div>
            <div class="product-price">
                <h3 id="prod-price"></h3>
            </div>
            <div class="quantity">
                <label for="qty">Số lượng:</label>
                <input id="qty" type="number" value="1" min="1" />
            </div>
        <div class="actions">
            <button id="buy-now-btn" class="buy-now">Mua ngay</button>
            <button id="add-cart-btn" class="add-to-cart">🛒 Thêm vào giỏ hàng</button>
        </div>
        </div>
    </section>
    <section class="review-section">
        <h3>⭐ Đánh giá sản phẩm</h3>
        <div id="average-rating">Đang tải...</div>
        <div class="comment-box">
            <h4>Viết đánh giá của bạn:</h4>
            <select id="rating">
                <option value="5">5 ⭐ Tuyệt vời</option>
                <option value="4">4 ⭐ Tốt</option>
                <option value="3">3 ⭐ Trung bình</option>
                <option value="2">2 ⭐ Kém</option>
                <option value="1">1 ⭐ Tệ</option>
            </select>
            <textarea id="comment" placeholder="Chia sẻ cảm nhận..."></textarea>
            <button id="submit-review">Gửi đánh giá</button>
        </div>
        <div class="user-comments" id="comment-list"></div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/techshop-ai-template/assets/js/product_detail.js" defer></script>
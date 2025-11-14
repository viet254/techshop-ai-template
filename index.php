<?php
// Trang chủ TechShop AI
include __DIR__ . '/includes/header.php';
?>
<!-- Thay thế style để banner lớn hơn và chuẩn hóa kích thước ads -->
<style>
:root{
    --ads-width: 260px; /* chỉnh chiều rộng quảng cáo */
    --ads-gap: 24px;
    --banner-extend: 758px; /* ~20cm */
}

/* main chừa chỗ cho ads fixed hai bên */
.main-content.home-page {
    max-width: 1200px;
    margin: 24px calc(var(--ads-width) + var(--ads-gap)); /* chừa chỗ trái/phải dựa trên biến */
    padding: 0 12px;
    box-sizing: border-box;
}

/* Ads fixed hai bên: không cố định chiều cao ở CSS, sẽ set bằng JS */
.ads-left, .ads-right {
    position: fixed;
    left: 12px; /* ads-left override */
    right: 12px; /* ads-right override below */
    top: 0;
    transform: none;
    width: var(--ads-width);
    z-index: 60;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    background:#fff;
}
/* phân biệt vị trí */
.ads-left { left: 12px; right: auto; }
.ads-right { right: 12px; left: auto; }

/* ảnh trong ads lấp đầy khung */
.ads-left img, .ads-right img { width:100%; height:100%; object-fit:cover; display:block; }

/* Center layout giữ nguyên (menu + content) */
.center-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

/* inner two-column */
.center-inner {
    display: flex;
    gap: 18px;
    align-items: flex-start;
}

/* menu */
.categories-nav {
    width: 220px;
    flex: 0 0 220px;
    background:#fff;
    border-radius:8px;
    padding:10px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

/* Banner: mở rộng sang phải bằng margin-right âm */
.banner.welcome-banner {
    width: calc(100% + var(--banner-extend));
    margin-right: calc(-1 * var(--banner-extend));
    min-height: 360px;       /* cao hơn để "dài ra" */
    border-radius: 12px;
    padding: 40px 28px;      /* tăng padding để banner lớn */
    text-align: center;
    box-shadow: 0 8px 30px rgba(11,30,60,0.06);
    background: linear-gradient(90deg,#2f92ff,#56b0ff);
    color: #fff;
    font-size: 1.05rem;
    display:flex;
    flex-direction:column;
    justify-content:center;
    box-sizing: border-box;
}

/* sản phẩm */
.home-products { display:flex; gap:16px; flex-wrap:wrap; }

/* Responsive: trên màn nhỏ trả banner về bình thường */
@media (max-width: 900px) {
    .banner.welcome-banner {
        width: 100%;
        margin-right: 0;
        min-height: auto;
        padding: 20px;
    }
    .ads-left, .ads-right { display:none; }
    .main-content.home-page { margin: 24px auto; padding: 0 12px; }
    .center-inner { flex-direction: column; }
    .categories-nav { width:100%; flex: 0 0 auto; }
}
</style>

<main class="main-content home-page">
    <!-- Quảng cáo bên trái (fixed, nằm ngoài flow) -->
    <aside class="ads-left" aria-label="Quảng cáo trái">
        <img src="/techshop-ai-template/assets/images/ad_left.jpg" alt="Quảng cáo trái" />
    </aside>

    <!-- Phần giữa: menu bên cạnh banner và dưới banner là sản phẩm -->
    <div class="center-column">
        <div class="center-inner">
            <!-- Menu danh mục dọc (ở bên trái của center) -->
            <nav class="categories-nav" aria-label="Danh mục sản phẩm">
                <ul>
                    <li><a href="/techshop-ai-template/products.php?cat=Phụ kiện">Chuột - Bàn phím - Tai nghe</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=Laptop">Laptop</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=Linh kiện">Linh kiện Laptop</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=Màn hình - Loa">Màn hình - Loa</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=SSD">SSD</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=RAM">RAM</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=Thẻ nhớ">Thẻ nhớ</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=USB">USB</a></li>
                    <li><a href="/techshop-ai-template/products.php?cat=HDD">HDD</a></li>
                </ul>
            </nav>

            <!-- Nội dung chính (banner trên + sản phẩm dưới) -->
            <div class="center-content">
                <!-- Banner chào mừng -->
                <div class="banner welcome-banner" role="region" aria-label="Banner chào mừng">
                    <h2>Chào mừng đến với TechShop AI</h2>
                    <p>Trải nghiệm mua sắm thông minh với trợ lý AI.</p>
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
            </div>
        </div>
    </div>

    <!-- Quảng cáo bên phải (fixed, nằm ngoài flow) -->
    <aside class="ads-right" aria-label="Quảng cáo phải">
        <img src="/techshop-ai-template/assets/images/ad_right.jpg" alt="Quảng cáo phải" />
    </aside>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- JS: đồng bộ vị trí top của ads với cột giữa (bỏ việc set height) -->
<script>
(function(){
    function syncAdsToCenter(){
        var center = document.querySelector('.center-column');
        var adL = document.querySelector('.ads-left');
        var adR = document.querySelector('.ads-right');
        if(!center || !adL || !adR) return;

        // lấy bounding rect của center relative to document
        var rect = center.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var top = rect.top + scrollTop;

        // chỉ set top để căn vị trí dọc; không set height ở đây
        adL.style.top = top + 'px';
        adR.style.top = top + 'px';
    }

    window.addEventListener('load', syncAdsToCenter);
    window.addEventListener('resize', syncAdsToCenter);
    window.addEventListener('scroll', syncAdsToCenter);
    // cập nhật sau một khoảng nhỏ (ảnh load muộn)
    setTimeout(syncAdsToCenter, 300);
})();
</script>

<script src="/techshop-ai-template/assets/js/home.js" defer></script>
// JavaScript to load featured categories on the home page
document.addEventListener('DOMContentLoaded', async () => {
    // Section for laptops
    const laptopContainer = document.getElementById('home-laptop');
    const linhkienContainer = document.getElementById('home-linhkien');
    if (!laptopContainer || !linhkienContainer) return;
    try {
        // Fetch laptop products
        const resLaptop = await fetch('/techshop-ai-template/api/get_products.php?cat=Laptop');
        let laptops = await resLaptop.json();
        // Sắp xếp giảm dần theo id để sản phẩm mới nhất lên đầu
        laptops.sort((a, b) => b.id - a.id);
        // Giới hạn 8 sản phẩm hiển thị
        laptops = laptops.slice(0, 8);
        laptopContainer.innerHTML = laptops.map(p => `
            <div class="product-card">
                <img src="assets/images/${p.image}" alt="${p.name}" />
                <h4>${p.name}</h4>
                <p>${Number(p.price).toLocaleString()}₫</p>
                <button onclick="location.href='product_detail.php?id=${p.id}'">Xem chi tiết</button>
            </div>
        `).join('');
        // Fetch linh kiện products
        const resLinhkien = await fetch('/techshop-ai-template/api/get_products.php?cat=' + encodeURIComponent('Linh kiện'));
        let linhkiens = await resLinhkien.json();
        // Sắp xếp giảm dần theo id
        linhkiens.sort((a, b) => b.id - a.id);
        // Hiển thị tối đa 8 sản phẩm linh kiện
        linhkiens = linhkiens.slice(0, 8);
        linhkienContainer.innerHTML = linhkiens.map(p => `
            <div class="product-card">
                <img src="assets/images/${p.image}" alt="${p.name}" />
                <h4>${p.name}</h4>
                <p>${Number(p.price).toLocaleString()}₫</p>
                <button onclick="location.href='product_detail.php?id=${p.id}'">Xem chi tiết</button>
            </div>
        `).join('');
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
            // Áp dụng chiều cao nếu lớn hơn 0
            if (navHeight > 0) {
                leftImg.style.height = navHeight + 'px';
                rightImg.style.height = navHeight + 'px';
            }
        }
    }
    // Gọi một lần khi trang tải và khi thay đổi kích thước
    adjustAdHeight();
    window.addEventListener('resize', adjustAdHeight);

    /*
     * Bỏ xử lý vị trí quảng cáo bằng JavaScript để quảng cáo
     * di chuyển tự nhiên cùng trang. Việc cố định và canh lề
     * được thực hiện bằng CSS (position: sticky).
     */
});
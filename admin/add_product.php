<?php
// Trang thêm sản phẩm riêng (admin)
// Bắt buộc đăng nhập với quyền admin bằng admin_header.php
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';

// Lấy danh sách danh mục duy nhất để hiển thị trong dropdown
$categories = [];
$catRes = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
if ($catRes) {
    while ($row = $catRes->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
    <!-- Nội dung trang thêm sản phẩm -->
    <h2>Thêm sản phẩm</h2>
    <div class="admin-card">
        <form class="admin-form" action="create_product.php" method="post" enctype="multipart/form-data">
            <label>Tên sản phẩm:
                <input type="text" name="name" required />
            </label>
            <label>Giá (VND):
                <input type="number" name="price" step="0.01" required />
            </label>
            <label>Giá khuyến mãi (VND):
                <input type="number" name="sale_price" step="0.01" />
            </label>
            <label>Danh mục:
                <select name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tồn kho:
                <input type="number" name="stock" value="0" />
            </label>
            <label>Ảnh sản phẩm:
                <input type="file" name="image_file" accept="image/*" />
            </label>
            <label>Thông số kỹ thuật:
                <!-- Trường thông số kỹ thuật cho phép nhập nhiều dòng -->
                <textarea name="specs"></textarea>
            </label>
            <label>Mô tả:
                <textarea name="description"></textarea>
            </label>
            <button type="submit">Thêm</button>
        </form>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
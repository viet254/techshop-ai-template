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
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-plus text-primary"></i>
            </div>
            <div>
                Thêm sản phẩm mới
                <div class="page-title-subheading">Nhập thông tin sản phẩm và tải ảnh đại diện.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a class="btn btn-outline-secondary btn-shadow" href="/admin/manage_products.php">Quay lại danh sách</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form class="admin-form" action="create_product.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="col-md-6">
                    <label>Tên sản phẩm
                        <input type="text" name="name" required />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Giá (VND)
                        <input type="number" name="price" step="0.01" required />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Giá khuyến mãi (VND)
                        <input type="number" name="sale_price" step="0.01" />
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-6">
                    <label>Danh mục
                        <select name="category" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Tồn kho
                        <input type="number" name="stock" value="0" />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Ảnh sản phẩm
                        <input type="file" name="image_file" accept="image/*" />
                    </label>
                </div>
            </div>
            <label>Thông số kỹ thuật
                <textarea name="specs" rows="4"></textarea>
            </label>
            <label>Mô tả
                <textarea name="description" rows="4"></textarea>
            </label>
            <button type="submit">Thêm sản phẩm</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
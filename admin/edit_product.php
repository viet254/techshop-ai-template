<?php
// Trang sửa sản phẩm (admin)
// Include admin header which handles authentication and prints <main>
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    echo '<p>Không tìm thấy sản phẩm.</p>';
    include __DIR__ . '/../includes/admin_footer.php';
    exit;
}
?>
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-note text-primary"></i>
            </div>
            <div>
                Chỉnh sửa sản phẩm #<?= $product['id'] ?>
                <div class="page-title-subheading">Cập nhật thông tin, giá và tồn kho sản phẩm.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a class="btn btn-outline-secondary btn-shadow" href="/admin/manage_products.php">Quay lại danh sách</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form class="admin-form" action="update_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $product['id'] ?>" />
            <div class="form-row">
                <div class="col-md-6">
                    <label>Tên sản phẩm
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Giá (VND)
                        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Giá khuyến mãi (VND)
                        <input type="number" name="sale_price" step="0.01" value="<?= isset($product['sale_price']) ? $product['sale_price'] : '' ?>" />
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-6">
                    <label>Danh mục
                        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Tồn kho
                        <input type="number" name="stock" value="<?= $product['stock'] ?>" />
                    </label>
                </div>
                <div class="col-md-3">
                    <label>Ảnh sản phẩm
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image']) ?>" />
                        <input type="file" name="image_file" accept="image/*" />
                        <?php if (!empty($product['image'])): ?>
                            <small class="text-muted d-block mt-1">Ảnh hiện tại: <?= htmlspecialchars($product['image']) ?></small>
                        <?php endif; ?>
                    </label>
                </div>
            </div>
            <label>Thông số kỹ thuật
                <textarea name="specs" rows="4"><?= htmlspecialchars($product['specs'] ?? '') ?></textarea>
            </label>
            <label>Mô tả
                <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
            </label>
            <button type="submit">Lưu thay đổi</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
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
    include __DIR__ . '/../includes/footer.php';
    exit;
}
?>
    <!-- Nội dung trang sửa sản phẩm -->
    <h2>Sửa sản phẩm #<?= $product['id'] ?></h2>
    <div class="admin-card">
    <form class="admin-form" action="update_product.php" method="post">
        <input type="hidden" name="id" value="<?= $product['id'] ?>" />
        <label>Tên sản phẩm:
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required />
        </label>
        <label>Giá (VND):
            <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required />
        </label>
        <label>Danh mục:
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" />
        </label>
        <label>Tồn kho:
            <input type="number" name="stock" value="<?= $product['stock'] ?>" />
        </label>
        <label>Tên file ảnh:
            <input type="text" name="image" value="<?= htmlspecialchars($product['image']) ?>" />
        </label>
        <label>Mô tả:
            <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
        </label>
        <button type="submit">Lưu thay đổi</button>
    </form>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
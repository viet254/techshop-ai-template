<?php
// Trang quản lý sản phẩm dành cho admin
// Include admin header (enforces admin authentication and opens <main>)
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';
// Lấy danh sách sản phẩm
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Lấy danh sách danh mục duy nhất từ bảng products để dùng cho dropdown
$categories = [];
$catRes = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
if ($catRes) {
    while ($row = $catRes->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
    <!-- Nội dung trang quản lý sản phẩm -->
    <!-- Link to separate add product page -->
    <h2>Quản lý sản phẩm <a href="add_product.php" class="btn-add-product">Thêm sản phẩm</a></h2>
    <div class="admin-card">
        <h3>Danh sách sản phẩm</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Giá</th>
                    <th>Giá khuyến mãi</th>
                    <th>Danh mục</th>
                    <th>Tồn kho</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price']) ?>₫</td>
                    <td>
                        <?php if (!empty($p['sale_price'])): ?>
                            <?= number_format($p['sale_price']) ?>₫
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['category']) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <?php
                        $imgFile = $p['image'];
                        $imgDir  = __DIR__ . '/../assets/images/';
                        $imgPath = '../assets/images/' . htmlspecialchars($imgFile);
                        if ($imgFile && file_exists($imgDir . $imgFile)) {
                            echo '<img src="' . $imgPath . '" alt="' . htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') . '" style="width:60px;height:auto;object-fit:cover;border-radius:4px;" />';
                        } else {
                            echo htmlspecialchars($imgFile, ENT_QUOTES, 'UTF-8');
                        }
                        ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-edit"><span class="icon">✏️</span> Sửa</a>
                        <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?');"><span class="icon">🗑️</span> Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
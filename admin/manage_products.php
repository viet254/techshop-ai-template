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
?>
    <!-- Nội dung trang quản lý sản phẩm -->
    <h2>Quản lý sản phẩm</h2>
    <div class="admin-card">
        <h3>Danh sách sản phẩm</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th><th>Tên</th><th>Giá</th><th>Danh mục</th><th>Tồn kho</th><th>Hình ảnh</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price']) ?>₫</td>
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
                        <a href="edit_product.php?id=<?= $p['id'] ?>">Sửa</a> |
                        <a href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="admin-card">
        <h3>Thêm sản phẩm mới</h3>
        <form class="admin-form" action="create_product.php" method="post">
            <label>Tên sản phẩm:
                <input type="text" name="name" required />
            </label>
            <label>Giá (VND):
                <input type="number" name="price" step="0.01" required />
            </label>
            <label>Danh mục:
                <input type="text" name="category" />
            </label>
            <label>Tồn kho:
                <input type="number" name="stock" value="0" />
            </label>
            <label>Tên file ảnh (trong assets/images):
                <input type="text" name="image" value="placeholder.jpg" />
            </label>
            <label>Mô tả:
                <textarea name="description"></textarea>
            </label>
            <button type="submit">Thêm</button>
        </form>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
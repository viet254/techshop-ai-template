<?php
// Trang quản lý sản phẩm dành cho admin
// Include admin header (enforces admin authentication and opens <main>)
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';

// Pagination settings
$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Từ khóa tìm kiếm (theo tên hoặc danh mục)
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

// Chuẩn bị điều kiện WHERE cho tìm kiếm
$whereSql = '';
if ($keyword !== '') {
    $whereSql = " WHERE name LIKE ? OR category LIKE ?";
    $kwLike = '%' . $keyword . '%';
}

// Đếm tổng số sản phẩm (có áp dụng tìm kiếm nếu có)
$totalRows = 0;
$countSql = "SELECT COUNT(*) AS cnt FROM products" . $whereSql;
$stmtCount = $conn->prepare($countSql);
if ($stmtCount) {
    if ($keyword !== '') {
        $stmtCount->bind_param('ss', $kwLike, $kwLike);
    }
    $stmtCount->execute();
    $countRes = $stmtCount->get_result();
    if ($countRes) {
        $row = $countRes->fetch_assoc();
        $totalRows = (int)$row['cnt'];
    }
    $stmtCount->close();
}
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Lấy danh sách sản phẩm theo trang (có áp dụng tìm kiếm nếu có)
$listSql = "SELECT * FROM products" . $whereSql . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmtProducts = $conn->prepare($listSql);
if ($keyword !== '') {
    $stmtProducts->bind_param('ssii', $kwLike, $kwLike, $perPage, $offset);
} else {
    $stmtProducts->bind_param('ii', $perPage, $offset);
}
$stmtProducts->execute();
$result = $stmtProducts->get_result();
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmtProducts->close();

// Giữ lại query string cho phân trang (nếu đang tìm kiếm)
$paginationQuery = $keyword !== '' ? '&q=' . urlencode($keyword) : '';
?>
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-box2 text-primary"></i>
            </div>
            <div>
                Quản lý sản phẩm
                <div class="page-title-subheading">Theo dõi tồn kho, giá bán và cập nhật sản phẩm.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="add_product.php" class="btn btn-primary btn-shadow">+ Thêm sản phẩm</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <div class="card-title mb-0">Danh sách sản phẩm</div>
            <small class="text-muted">Tổng <?= $totalRows ?> sản phẩm</small>
        </div>
        <form method="get" class="d-flex align-items-center gap-2">
            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                class="form-control"
                placeholder="Tìm theo tên hoặc danh mục..."
            />
            <button type="submit" class="btn btn-outline-secondary">
                Tìm kiếm
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle admin-table mb-0">
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
                        <td>#<?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price']) ?>₫</td>
                        <td>
                            <?php if (!empty($p['sale_price'])): ?>
                                <?= number_format($p['sale_price']) ?>₫
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td><span class="badge badge-pill badge-info"><?= $p['stock'] ?></span></td>
                        <td>
                            <?php
                            $imgFile = $p['image'];
                            $imgDir  = __DIR__ . '/../assets/images/';
                            $imgPath = '../assets/images/' . htmlspecialchars($imgFile);
                            if ($imgFile && file_exists($imgDir . $imgFile)) {
                                echo '<img src="' . $imgPath . '" alt="' . htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') . '" style="width:60px;height:60px;object-fit:cover;border-radius:8px;" />';
                            } else {
                                echo '<span class="text-muted">' . htmlspecialchars($imgFile, ENT_QUOTES, 'UTF-8') . '</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-edit btn btn-sm"><span class="icon">✏️</span> Sửa</a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn-delete btn btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');"><span class="icon">🗑️</span> Xóa</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination-wrap">
    <div class="pagination-circles">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $paginationQuery ?>" aria-label="Trang trước">←</a>
        <?php else: ?>
            <span class="disabled">←</span>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p == $page): ?>
                <span class="active"><?= $p ?></span>
            <?php else: ?>
                <a href="?page=<?= $p ?><?= $paginationQuery ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $paginationQuery ?>" aria-label="Trang sau">→</a>
        <?php else: ?>
            <span class="disabled">→</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
<?php
// Quản lý voucher (admin)
// Include admin header
include __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../database/connect.php';
// Lấy danh sách voucher
$result = $conn->query("SELECT * FROM vouchers ORDER BY id DESC");
$vouchers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
}
?>
<div class="app-page-title admin-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-ticket text-primary"></i>
            </div>
            <div>
                Quản lý voucher
                <div class="page-title-subheading">Quản trị mã giảm giá và trạng thái áp dụng.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <div class="card-title mb-0">Danh sách voucher</div>
            <small class="text-muted">Tổng <?= count($vouchers) ?> voucher</small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle admin-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th><th>Mã</th><th>Loại</th><th>Giá trị</th><th>Trạng thái</th><th>Hết hạn</th><th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td><?= htmlspecialchars($v['code']) ?></td>
                        <td><?= $v['discount_type'] === 'percent' ? 'Phần trăm' : 'Cố định' ?></td>
                        <td>
                            <?php
                            if ($v['discount_type'] === 'percent') {
                                echo floatval($v['discount_value']) . '%';
                            } else {
                                echo number_format($v['discount_value']) . '₫';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($v['active']): ?>
                                <span class="badge-soft badge-soft-success">Kích hoạt</span>
                            <?php else: ?>
                                <span class="badge-soft badge-soft-warning">Tạm ngưng</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($v['expiration_date']) ?></td>
                        <td>
                            <a href="delete_voucher.php?id=<?= $v['id'] ?>" class="btn-delete btn btn-sm" onclick="return confirm('Bạn có chắc muốn xóa voucher này?');"><span class="icon">🗑️</span> Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="card-title">Thêm voucher mới</div>
        <form class="admin-form" action="create_voucher.php" method="post">
            <div class="form-row">
                <div class="col-md-4">
                    <label>Mã voucher
                        <input type="text" name="code" required />
                    </label>
                </div>
                <div class="col-md-4">
                    <label>Loại giảm giá
                        <select name="discount_type">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền (VNĐ)</option>
                        </select>
                    </label>
                </div>
                <div class="col-md-4">
                    <label>Giá trị giảm
                        <input type="number" name="discount_value" step="0.01" required />
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-4">
                    <label>Ngày hết hạn
                        <input type="date" name="expiration_date" />
                    </label>
                </div>
            </div>
            <button type="submit">Thêm voucher</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
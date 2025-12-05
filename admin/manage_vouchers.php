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
    <h2>Quản lý voucher</h2>
    <div class="admin-card">
        <h3>Danh sách voucher</h3>
        <table class="admin-table">
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
                    <td><?= $v['active'] ? 'Kích hoạt' : 'Tạm ngưng' ?></td>
                    <td><?= htmlspecialchars($v['expiration_date']) ?></td>
                    <td>
                        <a href="delete_voucher.php?id=<?= $v['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa voucher này?');"><span class="icon">🗑️</span> Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="admin-card">
        <h3>Thêm voucher mới</h3>
        <form class="admin-form" action="create_voucher.php" method="post">
            <label>Mã voucher:
                <input type="text" name="code" required />
            </label>
            <label>Loại giảm giá:
                <select name="discount_type">
                    <option value="percent">Phần trăm (%)</option>
                    <option value="fixed">Số tiền (VNĐ)</option>
                </select>
            </label>
            <label>Giá trị giảm:
                <input type="number" name="discount_value" step="0.01" required />
            </label>
            <label>Ngày hết hạn:
                <input type="date" name="expiration_date" />
            </label>
            <button type="submit">Thêm voucher</button>
        </form>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
// Tạo đơn hàng khi thanh toán
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../includes/cart_service.php';
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thanh toán']);
    exit;
}
// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$addressId = isset($data['address_id']) ? (int)$data['address_id'] : 0;
$voucherCode = isset($data['voucher_code']) ? trim($data['voucher_code']) : '';
// Phương thức thanh toán (mặc định thanh toán khi nhận hàng)
$paymentMethod = isset($data['payment_method']) ? trim($data['payment_method']) : 'cod';

// Xác định người dùng
$userId = (int)$_SESSION['user']['id'];

// Kiểm tra địa chỉ: phải được cung cấp và thuộc về người dùng
if ($addressId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn địa chỉ giao hàng']);
    exit;
}
$stmtCheckAddr = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
$stmtCheckAddr->bind_param('ii', $addressId, $userId);
$stmtCheckAddr->execute();
$resAddr = $stmtCheckAddr->get_result();
if ($resAddr->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Địa chỉ không hợp lệ']);
    exit;
}

$items = cart_fetch_items($conn);
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
    exit;
}
$conn->begin_transaction();
try {
    // Khóa tồn kho từng sản phẩm để tránh race condition
    foreach ($items as $item) {
        $stmtLock = $conn->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
        $stmtLock->bind_param('i', $item['product_id']);
        $stmtLock->execute();
        $locked = $stmtLock->get_result()->fetch_assoc();
        if (!$locked || (int)$locked['stock'] < $item['quantity']) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Sản phẩm ' . $item['name'] . ' không đủ hàng']);
            exit;
        }
    }
} catch (Throwable $th) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Không thể xác thực tồn kho']);
    exit;
}
// Tính tổng dựa trên giá hiện tại
$total = 0;
foreach ($items as $item) {
    $total += $item['effective_price'] * $item['quantity'];
}

// Tính giảm giá (nếu có)
$discount = 0;
$finalTotal = $total;
if ($voucherCode !== '') {
    $stmtVoucher = $conn->prepare("SELECT discount_type, discount_value, active, expiration_date FROM vouchers WHERE code = ? LIMIT 1");
    $stmtVoucher->bind_param('s', $voucherCode);
    $stmtVoucher->execute();
    $resVoucher = $stmtVoucher->get_result();
    $voucher = $resVoucher->fetch_assoc();
    if ($voucher && $voucher['active'] && (!$voucher['expiration_date'] || $voucher['expiration_date'] >= date('Y-m-d'))) {
        if ($voucher['discount_type'] === 'percent') {
            $discount = $total * ($voucher['discount_value'] / 100);
        } else {
            $discount = $voucher['discount_value'];
        }
        if ($discount > $total) {
            $discount = $total;
        }
        $finalTotal = $total - $discount;
    } else {
        // Mã voucher không hợp lệ hoặc hết hạn
        $voucherCode = '';
    }
}

// Tạo đơn hàng
$stmtOrder = $conn->prepare("INSERT INTO orders (user_id, address_id, total, discount, final_total, voucher_code, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
$stmtOrder->bind_param('iiddis', $userId, $addressId, $total, $discount, $finalTotal, $voucherCode);
if (!$stmtOrder->execute()) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Không thể tạo đơn hàng']);
    exit;
}
$orderId = $conn->insert_id;
// Thêm order_items và cập nhật tồn kho
foreach ($items as $item) {
    $productId = $item['product_id'];
    $qty = $item['quantity'];
    $price = $item['effective_price'];
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmtItem->bind_param('iiid', $orderId, $productId, $qty, $price);
    $stmtItem->execute();
    $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmtStock->bind_param('ii', $qty, $productId);
    $stmtStock->execute();
}
// Xoá giỏ hàng: session và DB
unset($_SESSION['cart']);
$delCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
$delCart->bind_param('i', $userId);
$delCart->execute();
$conn->commit();
echo json_encode([
    'success'      => true,
    'order_id'     => $orderId,
    'total'        => $total,
    'discount'     => $discount,
    'final_total'  => $finalTotal,
    'voucher_code' => $voucherCode,
    'payment_method' => $paymentMethod
]);
?>
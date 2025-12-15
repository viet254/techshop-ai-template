<?php
// File: htdocs/admin/index.php
// Giao diện Admin dashboard (ArchitectUI-like)
// Lưu ý: cần session/login admin xử lý ở đầu nếu site của bạn yêu cầu.

session_start();
require_once __DIR__ . '/../database/connect.php';

// (Tuỳ) kiểm tra quyền admin
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
//}

// Lấy dữ liệu tĩnh ban đầu (fallback)
$stats = [
    'total_orders' => 0,
    'completed_orders' => 0,
    'total_users' => 0,
    'total_products' => 0
];

// Cố gắng lấy từ API nội bộ nếu có
$apiUrl = '/api/get_admin_stats.php';
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 2
]);
$res = curl_exec($ch);
if ($res !== false) {
    $json = json_decode($res, true);
    if (!empty($json['success']) && !empty($json['data'])) {
        $stats = array_merge($stats, $json['data']);
    }
}
curl_close($ch);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin - Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Custom admin CSS -->
  <link href="/assets/css/admin.css" rel="stylesheet">
</head>
<body>
  <div class="admin-wrapper d-flex">
    <!-- SIDEBAR -->
    <nav id="sidebar" class="sidebar bg-dark text-white">
      <div class="sidebar-header p-3 text-center">
        <a href="/admin" class="text-decoration-none text-white">
          <h4 class="mb-0">TechShop Admin</h4>
        </a>
      </div>
      <ul class="nav flex-column px-2">
        <li class="nav-item"><a class="nav-link text-white" href="/admin"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/admin/products.php"><i class="fa fa-box-open me-2"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/admin/orders.php"><i class="fa fa-shopping-cart me-2"></i> Orders</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/admin/users.php"><i class="fa fa-users me-2"></i> Users</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/admin/reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/admin/settings.php"><i class="fa fa-cog me-2"></i> Settings</a></li>
      </ul>
      <div class="sidebar-footer p-3 mt-auto">
        <small>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Admin'); ?></strong></small>
      </div>
    </nav>

    <!-- MAIN -->
    <div class="main flex-grow-1">
      <!-- TOPBAR -->
      <header class="topbar d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center">
          <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary me-3"><i class="fa fa-bars"></i></button>
          <h5 class="mb-0">Dashboard</h5>
        </div>
        <div class="d-flex align-items-center">
          <div class="me-3">
            <input id="admin-search" class="form-control form-control-sm" placeholder="Tìm sản phẩm, đơn hàng, người dùng..." />
          </div>
          <div class="dropdown">
            <a href="#" class="text-dark text-decoration-none" id="adminMenu" data-bs-toggle="dropdown">
              <img src="/assets/images/avatar-placeholder.png" class="rounded-circle" width="36" height="36" alt="avatar">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
              <li><a class="dropdown-item" href="/profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <!-- CONTENT -->
      <main class="content p-4">
        <div class="container-fluid">
          <div class="row g-3 mb-4">
            <!-- statistic cards -->
            <div class="col-12 col-md-6 col-lg-3">
              <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 display-6 text-primary"><i class="fa fa-receipt"></i></div>
                  <div>
                    <div class="fw-bold"><?php echo number_format($stats['total_orders']); ?></div>
                    <small class="text-muted">Tổng đơn hàng</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 display-6 text-success"><i class="fa fa-check-circle"></i></div>
                  <div>
                    <div class="fw-bold"><?php echo number_format($stats['completed_orders']); ?></div>
                    <small class="text-muted">Đơn hoàn thành</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 display-6 text-warning"><i class="fa fa-users"></i></div>
                  <div>
                    <div class="fw-bold"><?php echo number_format($stats['total_users']); ?></div>
                    <small class="text-muted">Người dùng</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 display-6 text-danger"><i class="fa fa-boxes"></i></div>
                  <div>
                    <div class="fw-bold"><?php echo number_format($stats['total_products']); ?></div>
                    <small class="text-muted">Sản phẩm</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- recent orders table -->
          <div class="row">
            <div class="col-12 col-lg-8 mb-3">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <strong>Đơn hàng mới</strong>
                  <a href="/admin/orders.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>#Mã</th>
                          <th>Khách hàng</th>
                          <th>Thành tiền</th>
                          <th>Trạng thái</th>
                          <th>Ngày</th>
                        </tr>
                      </thead>
                      <tbody id="recentOrdersBody">
                        <!-- sẽ load bằng JS fetch '/api/get_admin_orders.php' -->
                        <tr><td colspan="5" class="text-center py-4">Đang tải...</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- top products -->
            <div class="col-12 col-lg-4 mb-3">
              <div class="card">
                <div class="card-header"><strong>Sản phẩm nổi bật</strong></div>
                <div class="card-body">
                  <ul id="topProducts" class="list-unstyled mb-0">
                    <li class="py-2">Đang tải...</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- quick actions -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="card p-3">
                <strong>Quick actions</strong>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                  <a href="/admin/products.php" class="btn btn-outline-secondary btn-sm">Quản lý sản phẩm</a>
                  <a href="/admin/orders.php" class="btn btn-outline-secondary btn-sm">Quản lý đơn</a>
                  <a href="/admin/users.php" class="btn btn-outline-secondary btn-sm">Quản lý người dùng</a>
                  <a href="/admin/settings.php" class="btn btn-outline-secondary btn-sm">Cài đặt cửa hàng</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <footer class="p-3 text-center small text-muted">
        © <?php echo date('Y'); ?> TechShop — Admin Panel
      </footer>
    </div>
  </div>

  <!-- Bootstrap JS + dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom admin JS -->
  <script src="/assets/js/admin.js"></script>
</body>
</html>

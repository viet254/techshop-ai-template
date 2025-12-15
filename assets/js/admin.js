// File: assets/js/admin.js
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const main = document.querySelector('.main');
  const toggle = document.getElementById('sidebarToggle');

  if (toggle) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      main.classList.toggle('collapsed');
    });
  }

  // Load recent orders
  const ordersBody = document.getElementById('recentOrdersBody');
  fetch('/api/get_admin_orders.php')
    .then(r => r.json())
    .then(data => {
      if (!data || !data.orders) {
        ordersBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Không có dữ liệu</td></tr>';
        return;
      }
      ordersBody.innerHTML = '';
      data.orders.slice(0,8).forEach(o => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>#${o.id}</td>
                        <td>${o.customer_name || (o.user_id ? 'User#'+o.user_id : 'Khách vãng lai')}</td>
                        <td>${Number(o.final_total || o.total || 0).toLocaleString()}₫</td>
                        <td>${o.status || '-'}</td>
                        <td>${o.created_at ? (new Date(o.created_at)).toLocaleDateString() : '-'}</td>`;
        ordersBody.appendChild(tr);
      });
    }).catch(()=> {
      ordersBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Lỗi tải dữ liệu</td></tr>';
    });

  // Load top products
  const topList = document.getElementById('topProducts');
  fetch('/api/get_products.php?limit=5&sort=top')
    .then(r => r.json())
    .then(data => {
      if (!Array.isArray(data.products)) {
        topList.innerHTML = '<li>Không có dữ liệu</li>';
        return;
      }
      topList.innerHTML = '';
      data.products.slice(0,5).forEach(p => {
        const li = document.createElement('li');
        li.className = 'py-2 border-bottom';
        li.innerHTML = `<div class="d-flex align-items-center">
                          <img src="/assets/images/${p.image || 'placeholder.png'}" width="40" class="me-2" />
                          <div>
                            <div class="fw-bold">${p.name}</div>
                            <small class="text-muted">${Number(p.price).toLocaleString()}₫</small>
                          </div>
                        </div>`;
        topList.appendChild(li);
      });
    }).catch(()=> topList.innerHTML = '<li>Không thể tải</li>');
});

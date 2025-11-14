// JS for login page
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        try {
            const res = await fetch('/techshop-ai-template/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const data = await res.json();
            if (data.success) {
                // Nếu là admin, chuyển tới trang quản trị; ngược lại về trang chủ
                if (data.role && data.role === 'admin') {
                    location.href = '/techshop-ai-template/admin/dashboard.php';
                } else {
                    location.href = '/techshop-ai-template/index.php';
                }
            } else {
                alert(data.message || 'Đăng nhập thất bại.');
            }
        } catch (err) {
            alert('Không thể đăng nhập.');
        }
    });
});
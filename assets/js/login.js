// JS for login page
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        try {
            const res = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const data = await res.json();
            if (data.success) {
                // Determine redirect path based on role
                const redirectTo = (data.role && data.role === 'admin') ? '/admin/dashboard.php' : '/index.php';
                // Show success notification and redirect after a short delay
                showNotification('Đăng nhập thành công.', 'success');
                setTimeout(() => {
                    location.href = redirectTo;
                }, 1500);
            } else {
                showNotification(data.message || 'Đăng nhập thất bại.', 'error');
            }
        } catch (err) {
            showNotification('Không thể đăng nhập.', 'error');
        }
    });
});
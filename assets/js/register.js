// JS for registration page
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('reg-username').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;
        const confirm = document.getElementById('reg-confirm-password').value;
        if (password !== confirm) {
            // Password confirmation mismatch
            showNotification('Mật khẩu xác nhận không khớp.', 'error');
            return;
        }
        try {
            const res = await fetch('/api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });
            const data = await res.json();
            if (data.success) {
                // Notify success and then redirect to login after a short delay
                showNotification(data.message || 'Đăng ký thành công, vui lòng đăng nhập.', 'success');
                setTimeout(() => {
                    location.href = '/login.php';
                }, 1500);
            } else {
                showNotification(data.message || 'Đăng ký thất bại.', 'error');
            }
        } catch (err) {
            showNotification('Không thể đăng ký.', 'error');
        }
    });
});
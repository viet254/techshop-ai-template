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
            alert('Mật khẩu xác nhận không khớp.');
            return;
        }
        try {
            const res = await fetch('/techshop-ai-template/api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });
            const data = await res.json();
            if (data.success) {
                alert('Đăng ký thành công, vui lòng đăng nhập.');
                location.href = '/techshop-ai-template/login.php';
            } else {
                alert(data.message || 'Đăng ký thất bại.');
            }
        } catch (err) {
            alert('Không thể đăng ký.');
        }
    });
});
<?php
// Global footer include
?>
<footer class="footer">
    <div class="footer-content">
        <p>© 2025 TechShop AI. Bản quyền thuộc về TechShop AI.</p>
        <p>Liên hệ: <a href="mailto:support@techshop-ai.example.com">support@techshop-ai.example.com</a> | Hotline: 0123 456 789</p>
        <p>
            <a href="terms.php">Điều khoản dịch vụ</a> |
            <a href="privacy.php">Chính sách bảo mật</a>
        </p>
    </div>
</footer>
<!-- Chatbox AI -->
<div id="chatbox-container" class="chatbox-container">
    <button id="chatbox-toggle" class="chatbox-toggle">💬</button>
    <div id="chatbox-panel" class="chatbox-panel chatbox-hidden">
        <div class="chatbox-sidebar">
            <div class="chatbox-sidebar-header">
                <span>Lịch sử</span>
                <button class="chatbox-reset" id="chatbox-reset" title="Cuộc trò chuyện mới">Mới</button>
            </div>
            <div id="chatbox-session-list" class="chatbox-session-list">
                <div class="chatbox-session-empty">Chưa có lịch sử</div>
            </div>
            <div class="chatbox-sidebar-footer">
                <button id="chatbox-new" class="chatbox-new-btn">+ Cuộc chat mới</button>
            </div>
        </div>
        <div class="chatbox-main">
            <div class="chatbox-header">
                <span class="chatbox-title">Tư vấn sản phẩm</span>
                <div class="chatbox-header-actions">
                    <button class="chatbox-close" id="chatbox-close" title="Đóng">&times;</button>
                </div>
            </div>
            <div id="chatbox-messages" class="chatbox-messages"></div>
            <div class="chatbox-input-area">
                <input type="text" id="chatbox-input" placeholder="Nhập câu hỏi..." />
                <button id="chatbox-send" class="chatbox-send">Gửi</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/main.js"></script>
<script src="assets/js/supportbox.js"></script>
</body>
</html>

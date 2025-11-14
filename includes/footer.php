<?php
// Global footer include
?>
<footer class="footer">
    <div class="footer-content">
        <p>© 2025 TechShop AI. Bản quyền thuộc về TechShop AI.</p>
        <p>Liên hệ: <a href="mailto:support@techshop-ai.example.com">support@techshop-ai.example.com</a> | Hotline: 0123 456 789</p>
        <p>
            <a href="/techshop-ai-template/terms.php">Điều khoản dịch vụ</a> |
            <a href="/techshop-ai-template/privacy.php">Chính sách bảo mật</a>
        </p>
    </div>
</footer>

<!-- Chatbox AI toàn cục -->
<!-- Chatbox toggle button -->
<div id="chat-toggle" class="chat-toggle">💬</div>
<!-- Chatbox AI toàn cục (ẩn khi mới tải) -->
<div id="chatbox" class="hidden">
    <div id="chat-header">
        💬 Chat với AI
        <!-- Nút thu gọn chat -->
        <button id="collapseChat" title="Thu gọn" style="float:right; background:none; border:none; color:#fff; cursor:pointer; margin-left:5px;">✖</button>
        <!-- Nút làm mới chat -->
        <button id="clearChat" title="Xóa lịch sử chat" style="float:right; background:none; border:none; color:#fff; cursor:pointer;">🗑️</button>
    </div>
    <div id="chat-body"></div>
    <div id="chat-input-area">
        <input type="text" id="userInput" placeholder="Nhập câu hỏi..." />
        <button id="sendBtn">Gửi</button>
    </div>
</div>
</body>
</html>
<?php
// Proxy để tiếp nhận yêu cầu từ giao diện chat và chuyển tới API tư vấn sản phẩm
// Sử dụng cùng session để duy trì trạng thái cuộc hội thoại

// Thiết lập tiêu đề JSON
header('Content-Type: application/json; charset=utf-8');
// Kết nối session
session_start();

// Tất cả logic xử lý sẽ nằm trong api/ai_support.php; chỉ cần require file đó.
require_once __DIR__ . '/api/ai_support.php';
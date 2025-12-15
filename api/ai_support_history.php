<?php
// API lấy danh sách cuộc trò chuyện và lịch sử tin nhắn theo user đăng nhập
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/connect.php';

// Hỗ trợ cả GET lẫn POST; ưu tiên POST JSON
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
}

$currentUserId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
if ($currentUserId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xem lịch sử chat']);
    exit;
}

$sessionId = 0;
if (isset($input['session_id'])) {
    $sessionId = (int)$input['session_id'];
} elseif (isset($_GET['session_id'])) {
    $sessionId = (int)$_GET['session_id'];
}

if ($sessionId > 0) {
    // Lấy chi tiết 1 cuộc trò chuyện + toàn bộ tin nhắn
    $messages = [];
    $session = null;

    $stmt = $conn->prepare(
        "SELECT id, user_id, title, created_at, updated_at
         FROM ai_chat_sessions
         WHERE id = ? AND user_id = ? LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('ii', $sessionId, $currentUserId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $session = $res->fetch_assoc();
        }
        $stmt->close();
    }

    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'Cuộc trò chuyện không tồn tại hoặc không thuộc tài khoản này']);
        exit;
    }

    $stmtMsg = $conn->prepare(
        "SELECT id, sender, message, created_at
         FROM ai_chat_messages
         WHERE session_id = ? AND user_id = ?
         ORDER BY id ASC"
    );
    if ($stmtMsg) {
        $stmtMsg->bind_param('ii', $sessionId, $currentUserId);
        if ($stmtMsg->execute()) {
            $res = $stmtMsg->get_result();
            while ($row = $res->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        $stmtMsg->close();
    }

    echo json_encode([
        'success'  => true,
        'session'  => $session,
        'messages' => $messages,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy danh sách tất cả cuộc trò chuyện của user (sắp xếp mới nhất)
$sessions = [];
$stmt = $conn->prepare(
    "SELECT id, title, created_at, updated_at
     FROM ai_chat_sessions
     WHERE user_id = ?
     ORDER BY updated_at DESC
     LIMIT 50"
);
if ($stmt) {
    $stmt->bind_param('i', $currentUserId);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $sessions[] = $row;
        }
    }
    $stmt->close();
}

echo json_encode([
    'success'  => true,
    'sessions' => $sessions,
], JSON_UNESCAPED_UNICODE);



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

// Hàm xóa một session và tất cả tin nhắn của nó
function deleteSessionAndMessages($conn, $sessionId, $userId) {
    // Xóa tất cả tin nhắn của session này
    $stmtDeleteMsg = $conn->prepare(
        "DELETE FROM ai_chat_messages WHERE session_id = ? AND user_id = ?"
    );
    if ($stmtDeleteMsg) {
        $stmtDeleteMsg->bind_param('ii', $sessionId, $userId);
        $stmtDeleteMsg->execute();
        $stmtDeleteMsg->close();
    }
    
    // Xóa session
    $stmtDeleteSession = $conn->prepare(
        "DELETE FROM ai_chat_sessions WHERE id = ? AND user_id = ?"
    );
    if ($stmtDeleteSession) {
        $stmtDeleteSession->bind_param('ii', $sessionId, $userId);
        $result = $stmtDeleteSession->execute();
        $stmtDeleteSession->close();
        return $result;
    }
    return false;
}

// Tự động xóa các cuộc trò chuyện cũ hơn 30 ngày khi load danh sách
function autoCleanOldSessions($conn, $userId, $daysOld = 30) {
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
    
    // Lấy danh sách sessions cũ cần xóa
    $stmt = $conn->prepare(
        "SELECT id FROM ai_chat_sessions 
         WHERE user_id = ? AND updated_at < ?"
    );
    if ($stmt) {
        $stmt->bind_param('is', $userId, $cutoffDate);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $deletedCount = 0;
            while ($row = $res->fetch_assoc()) {
                if (deleteSessionAndMessages($conn, $row['id'], $userId)) {
                    $deletedCount++;
                }
            }
            $stmt->close();
            return $deletedCount;
        }
        $stmt->close();
    }
    return 0;
}

// Xử lý xóa session nếu có action = 'delete'
$action = isset($input['action']) ? $input['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
if ($action === 'delete') {
    $deleteSessionId = isset($input['session_id']) ? (int)$input['session_id'] : (isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0);
    
    if ($deleteSessionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID session không hợp lệ']);
        exit;
    }
    
    // Kiểm tra session có thuộc về user này không
    $stmtCheck = $conn->prepare(
        "SELECT id FROM ai_chat_sessions WHERE id = ? AND user_id = ? LIMIT 1"
    );
    if ($stmtCheck) {
        $stmtCheck->bind_param('ii', $deleteSessionId, $currentUserId);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if (!$resCheck->fetch_assoc()) {
            $stmtCheck->close();
            echo json_encode(['success' => false, 'message' => 'Cuộc trò chuyện không tồn tại hoặc không thuộc tài khoản này']);
            exit;
        }
        $stmtCheck->close();
    }
    
    // Xóa session sử dụng hàm helper
    if (deleteSessionAndMessages($conn, $deleteSessionId, $currentUserId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa cuộc trò chuyện thành công'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa cuộc trò chuyện']);
    }
    exit;
}

// Xử lý xóa tất cả cuộc trò chuyện cũ
if ($action === 'delete_old') {
    $daysOld = isset($input['days']) ? (int)$input['days'] : 30;
    if ($daysOld < 1) {
        $daysOld = 30;
    }
    
    $deletedCount = autoCleanOldSessions($conn, $currentUserId, $daysOld);
    echo json_encode([
        'success' => true,
        'message' => "Đã xóa {$deletedCount} cuộc trò chuyện cũ thành công",
        'deleted_count' => $deletedCount
    ], JSON_UNESCAPED_UNICODE);
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

// Tự động xóa các cuộc trò chuyện cũ hơn 30 ngày trước khi lấy danh sách
autoCleanOldSessions($conn, $currentUserId, 30);

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



<?php
// Chatbox AI handler
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($data['message'] ?? '');
if (!$userMessage) {
    echo json_encode([ 'reply' => 'Xin chào! Bạn hãy nhập câu hỏi của mình.' ]);
    exit;
}
// Đây là nơi gọi API OpenAI. Thay thế API_KEY bằng khóa của bạn.
$api_key = 'sk-REPLACE_WITH_YOUR_OPENAI_API_KEY';

if ($api_key !== 'sk-REPLACE_WITH_YOUR_OPENAI_API_KEY') {
    // Nếu đã cấu hình API KEY, gửi yêu cầu tới OpenAI
    $url = 'https://api.openai.com/v1/chat/completions';
    $postData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Bạn là trợ lý AI của TechShop. Hãy trả lời ngắn gọn và thân thiện.'],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'temperature' => 0.7,
        'max_tokens' => 150
    ];
    // Sử dụng cURL nếu có, để tăng khả năng thành công
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);
        if ($result && !$err) {
            $response = json_decode($result, true);
            $reply = $response['choices'][0]['message']['content'] ?? 'Xin lỗi, có lỗi xảy ra.';
            echo json_encode([ 'reply' => $reply ]);
            exit;
        }
    } else {
        // Fallback to file_get_contents if curl unavailable
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ];
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($postData),
                'timeout' => 10
            ]
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        if ($result) {
            $response = json_decode($result, true);
            $reply = $response['choices'][0]['message']['content'] ?? 'Xin lỗi, có lỗi xảy ra.';
            echo json_encode([ 'reply' => $reply ]);
            exit;
        }
    }
}
// Phản hồi mặc định nếu chưa cấu hình API hoặc lỗi
$responses = [
    'chào' => 'Xin chào! Tôi có thể giúp gì cho bạn?',
    'mua' => 'Bạn có thể thêm sản phẩm vào giỏ và tiến hành thanh toán khi sẵn sàng.',
    'giao hàng' => 'Shop giao hàng toàn quốc qua Giao Hàng Nhanh và Viettel Post.',
    'giá' => 'Bạn có thể xem giá trực tiếp trên từng sản phẩm.',
];
foreach ($responses as $keyword => $reply) {
    if (mb_stripos($userMessage, $keyword) !== false) {
        echo json_encode([ 'reply' => $reply ]);
        exit;
    }
}
echo json_encode([ 'reply' => 'Xin lỗi, tôi chưa hiểu câu hỏi của bạn.' ]);
?>
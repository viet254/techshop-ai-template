// Chatbox AI đơn giản: front-end
// Gửi câu hỏi tới chatbot_api.php và hiển thị trả lời.

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('ai-chat-toggle');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-chat-close');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const messagesBox = document.getElementById('ai-chat-messages');

    if (!toggleBtn || !panel || !closeBtn || !input || !sendBtn || !messagesBox) {
        // Nếu thiếu phần tử DOM thì không làm gì để tránh lỗi JS
        return;
    }

    function appendMessage(text, type) {
        const div = document.createElement('div');
        div.className = 'ai-msg ' + type;
        div.textContent = text;
        messagesBox.appendChild(div);
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function openPanel() {
        panel.classList.remove('hidden');
    }
    function closePanel() {
        panel.classList.add('hidden');
    }

    toggleBtn.addEventListener('click', () => {
        if (panel.classList.contains('hidden')) {
            openPanel();
        } else {
            closePanel();
        }
    });

    closeBtn.addEventListener('click', closePanel);

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        appendMessage(text, 'user');
        input.value = '';

        try {
            const res = await fetch('chatbot_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            if (data.reply) {
                appendMessage(data.reply, 'bot');
            } else if (data.error) {
                appendMessage('Lỗi: ' + data.error, 'bot');
            } else {
                appendMessage('Xin lỗi, không nhận được trả lời từ máy chủ.', 'bot');
            }
        } catch (err) {
            console.error(err);
            appendMessage('Không thể kết nối tới máy chủ.', 'bot');
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});

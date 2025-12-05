// assets/js/supportbox.js
document.addEventListener('DOMContentLoaded', () => {
    const panel     = document.getElementById('chatbox-panel');
    const toggleBtn = document.getElementById('chatbox-toggle');
    const closeBtn  = document.getElementById('chatbox-close');
    const inputEl   = document.getElementById('chatbox-input');
    const sendBtn   = document.getElementById('chatbox-send');
    const resetBtn  = document.getElementById('chatbox-reset');

    if (!panel || !toggleBtn || !closeBtn || !inputEl || !sendBtn) {
        console.warn('Chatbox elements not found');
        return;
    }

    const STORAGE_KEY = 'techshop_ai_chat_history';
    let chatHistory = [];
    let nextMessageStartsNewChat = false;

    // ====== MỞ / ĐÓNG KHUNG CHAT ======
    toggleBtn.addEventListener('click', () => {
        panel.classList.remove('chatbox-hidden');
        inputEl.focus();
    });

    closeBtn.addEventListener('click', () => {
        panel.classList.add('chatbox-hidden');
    });

    // ====== NÚT CUỘC TRÒ CHUYỆN MỚI ======
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            clearChatHistory();
            nextMessageStartsNewChat = true;
            appendMessage('bot', 'Đã bắt đầu cuộc trò chuyện mới. Bạn muốn tư vấn sản phẩm nào?');
        });
    }

    // ====== GỬI TIN NHẮN ======
    sendBtn.addEventListener('click', sendMessage);

    inputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    async function sendMessage() {
        const text = inputEl.value.trim();
        if (!text) return;

        // Hiện ngay tin nhắn của KHÁCH
        appendMessage('user', text);
        inputEl.value = '';

        const body = { message: text };
        if (nextMessageStartsNewChat) {
            body.new_chat = true;
            nextMessageStartsNewChat = false;
        }

        try {
            const res = await fetch('api/ai_support.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            if (!res.ok) {
                appendMessage('bot', 'Không thể kết nối tới máy chủ.');
                return;
            }

            const raw = await res.text();
            let data = null;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                console.warn('Response is not valid JSON, raw = ', raw);
            }

            if (!data || typeof data !== 'object') {
                appendMessage('bot', raw || 'Máy chủ trả về dữ liệu không hợp lệ.');
                return;
            }

            if (!data.success) {
                appendMessage('bot', data.reply || 'Có lỗi xảy ra khi xử lý yêu cầu.');
            } else {
                appendMessage('bot', data.reply);
            }
        } catch (err) {
            console.error(err);
            appendMessage('bot', 'Không thể kết nối tới máy chủ.');
        }
    }

    // ====== HIỂN THỊ TIN NHẮN + LƯU LỊCH SỬ ======
    function appendMessage(sender, text, save = true) {
        const messages = document.getElementById('chatbox-messages');
        if (!messages) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('chatbox-message');
        if (sender === 'user') {
            wrapper.classList.add('chatbox-message-user');
        } else {
            wrapper.classList.add('chatbox-message-bot');
        }

        const label = document.createElement('div');
        label.classList.add('chatbox-label');
        label.textContent = sender === 'user' ? 'Bạn' : 'Tư vấn AI';

        const bubble = document.createElement('div');
        bubble.classList.add('chatbox-bubble');

        if (sender === 'bot') {
            bubble.innerHTML = formatBotText(text);
        } else {
            bubble.textContent = text;
        }

        wrapper.appendChild(label);
        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;

        if (save) {
            chatHistory.push({ sender, text });
            saveHistory();
        }
    }

    function saveHistory() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
        } catch (e) {
            console.warn('Cannot save chat history', e);
        }
    }

    function loadHistory() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const arr = JSON.parse(raw);
            if (!Array.isArray(arr)) return;
            chatHistory = arr;
            chatHistory.forEach(msg => appendMessage(msg.sender, msg.text, false));
        } catch (e) {
            console.warn('Cannot load chat history', e);
        }
    }

    function clearChatHistory() {
        chatHistory = [];
        localStorage.removeItem(STORAGE_KEY);
        const messages = document.getElementById('chatbox-messages');
        if (messages) messages.innerHTML = '';
    }

    // ====== FORMAT BOT TEXT (xuống dòng, bullet, link) ======
    function formatBotText(text) {
        if (!text) return '';

        let safe = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // **bold**
        safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // [text](url)
        safe = safe.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

        const lines = safe.split('\n');
        let html = '';
        let inList = false;

        for (let line of lines) {
            if (/^\s*[-*•]/.test(line)) {
                if (!inList) {
                    inList = true;
                    html += '<ul style="padding-left:18px;margin:4px 0;">';
                }
                line = line.replace(/^\s*[-*•]\s*/, '');
                html += '<li>' + line + '</li>';
            } else {
                if (inList) {
                    inList = false;
                    html += '</ul>';
                }
                if (line.trim() !== '') {
                    html += line + '<br>';
                } else {
                    html += '<br>';
                }
            }
        }
        if (inList) html += '</ul>';

        return html;
    }

    // Tải lịch sử khi mở trang
    loadHistory();
});

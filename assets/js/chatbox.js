// Chatbox AI functionality
document.addEventListener('DOMContentLoaded', () => {
    const chatHeader = document.getElementById('chat-header');
    const chatBody = document.getElementById('chat-body');
    const chatbox = document.getElementById('chatbox');
    const chatToggle = document.getElementById('chat-toggle');
    const input = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const clearBtn = document.getElementById('clearChat');
    const collapseBtn = document.getElementById('collapseChat');

    // Xác định khóa lưu lịch sử chat dựa trên USER_ID
    const historyKey = (typeof USER_ID !== 'undefined' && USER_ID !== null) ? 'chatHistory_' + USER_ID : 'chatHistory_guest';
    let chatHistory = [];

    // Tải lịch sử chat từ localStorage
    function loadHistory() {
        try {
            const stored = localStorage.getItem(historyKey);
            if (stored) {
                chatHistory = JSON.parse(stored);
                chatHistory.forEach(item => {
                    addMessage(item.sender, item.text, false);
                });
            }
        } catch (e) {
            console.error('Không thể tải lịch sử chat:', e);
        }
    }

    // Lưu lịch sử chat vào localStorage
    function saveHistory() {
        try {
            localStorage.setItem(historyKey, JSON.stringify(chatHistory));
        } catch (e) {
            console.error('Không thể lưu lịch sử chat:', e);
        }
    }

    // Toggle chatbox visibility
    if (chatHeader) {
        chatHeader.addEventListener('click', () => {
            chatBody.classList.toggle('hidden');
            document.getElementById('chat-input-area').classList.toggle('hidden');
        });
    }
    // Toggle chatbox open/close when clicking the floating button
    if (chatToggle && chatbox) {
        chatToggle.addEventListener('click', () => {
            // Show or hide chatbox
            chatbox.classList.toggle('hidden');
            // Hide or show the toggle button itself when chatbox is open
            chatToggle.classList.toggle('hidden');
        });
        // When clicking the header while chatbox is open, we do not hide the entire chatbox
        // We keep this behaviour to toggle content only (done above)
    }

    // Collapse chatbox when clicking the collapse button (X)
    if (collapseBtn) {
        collapseBtn.addEventListener('click', () => {
            chatbox.classList.add('hidden');
            if (chatToggle) chatToggle.classList.remove('hidden');
        });
    }

    // Thêm tin nhắn vào chatbody và cập nhật lịch sử (skipPush dùng khi tải lịch sử)
    function addMessage(sender, text, skipPush = false) {
        const msg = document.createElement('div');
        msg.className = `msg ${sender}`;
        msg.textContent = text;
        chatBody.appendChild(msg);
        chatBody.scrollTop = chatBody.scrollHeight;
        if (!skipPush) {
            chatHistory.push({ sender, text });
            saveHistory();
        }
    }

    // Send message to server
    async function sendMessage() {
        const message = input.value.trim();
        if (!message) return;
        addMessage('user', message);
        input.value = '';
        try {
            const res = await fetch('/chatbot/chat_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            addMessage('bot', data.reply);
        } catch (err) {
            addMessage('bot', 'Lỗi máy chủ. Vui lòng thử lại sau.');
        }
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }
    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    // Nút xóa lịch sử chat
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            chatHistory = [];
            localStorage.removeItem(historyKey);
            chatBody.innerHTML = '';
        });
    }

    // Khi trang tải xong, hiển thị lại lịch sử chat nếu có
    loadHistory();
});
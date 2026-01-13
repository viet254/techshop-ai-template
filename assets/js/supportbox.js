// assets/js/supportbox.js
document.addEventListener('DOMContentLoaded', () => {
    const panel     = document.getElementById('chatbox-panel');
    const toggleBtn = document.getElementById('chatbox-toggle');
    const closeBtn  = document.getElementById('chatbox-close');
    const inputEl   = document.getElementById('chatbox-input');
    const sendBtn   = document.getElementById('chatbox-send');
    const resetBtn  = document.getElementById('chatbox-reset');
    const newBtn    = document.getElementById('chatbox-new');
    const messages  = document.getElementById('chatbox-messages');
    const sessionList = document.getElementById('chatbox-session-list');

    if (!panel || !toggleBtn || !closeBtn || !inputEl || !sendBtn || !messages || !sessionList) {
        console.warn('Chatbox elements not found');
        return;
    }

    let nextMessageStartsNewChat = false;
    let currentSessionId = 0;
    let sessions = [];

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
            startNewChat();
        });
    }
    if (newBtn) {
        newBtn.addEventListener('click', () => {
            startNewChat();
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
        if (nextMessageStartsNewChat || currentSessionId === 0) {
            body.new_chat = true;
        }
        if (currentSessionId > 0) {
            body.session_id = currentSessionId;
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
                if (data.session_id) {
                    currentSessionId = data.session_id;
                }
                nextMessageStartsNewChat = false;
                // Sau khi nhận trả lời, refresh danh sách phiên để hiển thị lên đầu
                loadSessions(false, currentSessionId);
            }
        } catch (err) {
            console.error(err);
            appendMessage('bot', 'Không thể kết nối tới máy chủ.');
        }
    }

    // ====== HIỂN THỊ TIN NHẮN ======
    function appendMessage(sender, text) {
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
    }

    function clearMessagesUI() {
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

    // ====== LẤY DANH SÁCH CUỘC TRÒ CHUYỆN ======
    async function loadSessions(selectIfEmpty = true, keepActiveId = 0) {
        try {
            const res = await fetch('api/ai_support_history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            });
            if (!res.ok) {
                // Kiểm tra nếu là lỗi 401 (Unauthorized) - chưa đăng nhập
                if (res.status === 401) {
                    renderSessionError('Vui lòng đăng nhập để xem lịch sử chat', true);
                } else {
                    renderSessionError('Không thể tải lịch sử (HTTP ' + res.status + ')');
                }
                return;
            }
            const data = await res.json();
            if (!data.success) {
                // Kiểm tra nếu message có chứa "đăng nhập" thì hiển thị nút đăng nhập
                const isLoginRequired = data.message && (
                    data.message.toLowerCase().includes('đăng nhập') ||
                    data.message.toLowerCase().includes('login')
                );
                renderSessionError(data.message || 'Không thể tải lịch sử', isLoginRequired);
                return;
            }
            sessions = data.sessions || [];
            renderSessions();

            // Nếu chưa có phiên đang chọn thì chọn phiên mới nhất
            if (selectIfEmpty && currentSessionId === 0 && sessions.length > 0) {
                selectSession(sessions[0].id);
            }

            // Nếu có keepActiveId và list mới có id đó, giữ active
            if (keepActiveId > 0 && sessions.some(s => s.id === keepActiveId)) {
                setActiveSession(keepActiveId);
            }
        } catch (err) {
            console.error(err);
            renderSessionError('Không thể tải lịch sử');
        }
    }

    function renderSessionError(text, isUnauthorized = false) {
        if (isUnauthorized) {
            // Hiển thị nút đăng nhập khi chưa đăng nhập
            sessionList.innerHTML = `
                <div class="chatbox-session-empty" style="text-align: center; padding: 20px;">
                    <p style="margin-bottom: 15px; color: #666;">${text}</p>
                    <button id="chatbox-login-btn" style="
                        padding: 10px 20px;
                        background: #007bff;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        transition: background 0.2s;
                    ">Đăng nhập</button>
                </div>
            `;
            // Thêm event listener cho nút đăng nhập
            const loginBtn = document.getElementById('chatbox-login-btn');
            if (loginBtn) {
                loginBtn.addEventListener('click', () => {
                    window.location.href = '/login.php';
                });
                loginBtn.addEventListener('mouseenter', () => {
                    loginBtn.style.background = '#0056b3';
                });
                loginBtn.addEventListener('mouseleave', () => {
                    loginBtn.style.background = '#007bff';
                });
            }
        } else {
            sessionList.innerHTML = `<div class="chatbox-session-empty">${text}</div>`;
        }
    }

    function renderSessions() {
        if (!Array.isArray(sessions) || sessions.length === 0) {
            renderSessionError('Chưa có lịch sử');
            return;
        }

        sessionList.innerHTML = '';
        
        // Thêm nút xóa tất cả cuộc trò chuyện cũ
        const deleteOldBtn = document.createElement('button');
        deleteOldBtn.className = 'chatbox-delete-old-btn';
        deleteOldBtn.textContent = '🗑️ Xóa tất cả cuộc trò chuyện cũ (>30 ngày)';
        deleteOldBtn.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 8px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; color: #495057; font-size: 13px; transition: background 0.2s;';
        deleteOldBtn.addEventListener('click', deleteOldSessions);
        deleteOldBtn.addEventListener('mouseenter', () => {
            deleteOldBtn.style.background = '#e9ecef';
        });
        deleteOldBtn.addEventListener('mouseleave', () => {
            deleteOldBtn.style.background = '#f8f9fa';
        });
        sessionList.appendChild(deleteOldBtn);
        
        sessions.forEach(sess => {
            const item = document.createElement('div');
            item.className = 'chatbox-session-item';
            item.dataset.id = sess.id;
            if (sess.id === currentSessionId) {
                item.classList.add('active');
            }
            
            const contentWrapper = document.createElement('div');
            contentWrapper.style.display = 'flex';
            contentWrapper.style.justifyContent = 'space-between';
            contentWrapper.style.alignItems = 'center';
            contentWrapper.style.width = '100%';
            
            const textWrapper = document.createElement('div');
            textWrapper.style.flex = '1';
            textWrapper.style.minWidth = '0';
            
            const title = document.createElement('div');
            title.className = 'chatbox-session-title';
            title.textContent = sess.title || `Cuộc trò chuyện #${sess.id}`;

            const meta = document.createElement('div');
            meta.className = 'chatbox-session-meta';
            meta.textContent = formatDate(sess.updated_at || sess.created_at);

            textWrapper.appendChild(title);
            textWrapper.appendChild(meta);
            
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'chatbox-session-delete';
            deleteBtn.innerHTML = '×';
            deleteBtn.title = 'Xóa cuộc trò chuyện';
            deleteBtn.style.cssText = 'background: transparent; border: none; color: #999; cursor: pointer; font-size: 24px; line-height: 1; padding: 0 8px; margin-left: 8px; flex-shrink: 0; transition: color 0.2s;';
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteSession(sess.id);
            });
            deleteBtn.addEventListener('mouseenter', () => {
                deleteBtn.style.color = '#e74c3c';
            });
            deleteBtn.addEventListener('mouseleave', () => {
                deleteBtn.style.color = '#999';
            });
            
            contentWrapper.appendChild(textWrapper);
            contentWrapper.appendChild(deleteBtn);
            item.appendChild(contentWrapper);
            item.addEventListener('click', (e) => {
                if (e.target !== deleteBtn && !deleteBtn.contains(e.target)) {
                    selectSession(sess.id);
                }
            });
            sessionList.appendChild(item);
        });
    }

    function setActiveSession(id) {
        currentSessionId = id;
        document.querySelectorAll('.chatbox-session-item').forEach(el => {
            if (Number(el.dataset.id) === id) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    async function selectSession(id) {
        currentSessionId = id;
        setActiveSession(id);
        clearMessagesUI();
        try {
            const res = await fetch('api/ai_support_history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ session_id: id })
            });
            if (!res.ok) {
                appendMessage('bot', 'Không thể tải lịch sử cuộc trò chuyện này (HTTP ' + res.status + ').');
                return;
            }
            const data = await res.json();
            if (!data.success) {
                appendMessage('bot', data.message || 'Không thể tải lịch sử cuộc trò chuyện này.');
                return;
            }
            const msgs = data.messages || [];
            msgs.forEach(m => appendMessage(m.sender === 'bot' ? 'bot' : 'user', m.message));
            nextMessageStartsNewChat = false;
        } catch (err) {
            console.error(err);
            appendMessage('bot', 'Không thể tải lịch sử cuộc trò chuyện này.');
        }
    }

    function startNewChat() {
        clearMessagesUI();
        appendMessage('bot', 'Đã bắt đầu cuộc trò chuyện mới. Bạn muốn tư vấn sản phẩm nào?');
        nextMessageStartsNewChat = true;
        currentSessionId = 0;
        setActiveSession(-1);
    }

    function formatDate(str) {
        if (!str) return '';
        const d = new Date(str);
        if (Number.isNaN(d.getTime())) return str;
        return d.toLocaleString('vi-VN', { hour12: false });
    }

    // ====== XÓA CUỘC TRÒ CHUYỆN ======
    async function deleteSession(sessionId) {
        if (!confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này không?')) {
            return;
        }

        try {
            const res = await fetch('api/ai_support_history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'delete',
                    session_id: sessionId
                })
            });

            if (!res.ok) {
                alert('Không thể xóa cuộc trò chuyện (HTTP ' + res.status + ')');
                return;
            }

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Không thể xóa cuộc trò chuyện');
                return;
            }

            // Nếu đang xem session bị xóa thì xóa UI và reset
            if (sessionId === currentSessionId) {
                clearMessagesUI();
                currentSessionId = 0;
                setActiveSession(-1);
                appendMessage('bot', 'Đã xóa cuộc trò chuyện. Bạn muốn bắt đầu cuộc trò chuyện mới?');
            }

            // Reload danh sách sessions
            loadSessions(false, currentSessionId);
        } catch (err) {
            console.error(err);
            alert('Không thể kết nối tới máy chủ để xóa cuộc trò chuyện');
        }
    }

    // ====== XÓA TẤT CẢ CUỘC TRÒ CHUYỆN CŨ ======
    async function deleteOldSessions() {
        if (!confirm('Bạn có chắc chắn muốn xóa tất cả cuộc trò chuyện cũ hơn 30 ngày không?')) {
            return;
        }

        try {
            const res = await fetch('api/ai_support_history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'delete_old',
                    days: 30
                })
            });

            if (!res.ok) {
                alert('Không thể xóa cuộc trò chuyện cũ (HTTP ' + res.status + ')');
                return;
            }

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Không thể xóa cuộc trò chuyện cũ');
                return;
            }

            const deletedCount = data.deleted_count || 0;
            if (deletedCount > 0) {
                alert(`Đã xóa ${deletedCount} cuộc trò chuyện cũ thành công.`);
            } else {
                alert('Không có cuộc trò chuyện cũ nào để xóa.');
            }

            // Reload danh sách sessions
            loadSessions(false, currentSessionId);
        } catch (err) {
            console.error(err);
            alert('Không thể kết nối tới máy chủ để xóa cuộc trò chuyện cũ');
        }
    }

    // Tải danh sách cuộc trò chuyện khi mở trang
    loadSessions(true);
});

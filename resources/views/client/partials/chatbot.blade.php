<!-- AI Chatbot Widget -->
<div id="ai-chatbot" class="fixed bottom-6 right-6 z-50 flex flex-col items-end font-ui">
    <!-- Chat Window (Hidden by default) -->
    <div id="chat-window" class="hidden w-[calc(100vw-2rem)] sm:w-[440px] md:w-[480px] bg-white rounded-2xl shadow-xl border border-brand-border overflow-hidden mb-4 transform transition-all duration-300 origin-bottom-right scale-95 opacity-0 flex flex-col">
        <!-- Header -->
        <div class="bg-brand-accent text-white px-4 py-3 flex justify-between items-center rounded-t-2xl shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"></path></svg>
                <p class="font-semibold text-sm">MNM AI Assistant</p>
            </div>
            <button id="close-chat" aria-label="Đóng cửa sổ chat" class="text-white/80 hover:text-white transition-colors" type="button">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Chat Body -->
        <div id="chat-messages" class="h-[440px] sm:h-[480px] max-h-[65vh] overflow-y-auto p-4 bg-brand-surface flex flex-col gap-3 text-sm">
            <!-- Bot Message -->
            <div class="flex gap-2 w-max max-w-[85%]">
                <div class="w-7 h-7 rounded-full bg-brand-accent flex items-center justify-center flex-shrink-0 mt-auto mb-1">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="bg-white border border-brand-border px-3 py-2 rounded-2xl rounded-bl-sm text-brand-text shadow-sm leading-relaxed">
                    Xin chào! Tôi là AI ảo của MNM Bookstore 📚. Tôi có thể giúp gì cho bạn hôm nay?
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-3 bg-white border-t border-brand-border">
            <form id="chat-form" class="flex gap-2 items-center">
                <input type="text" id="chat-input" aria-label="Nhập tin nhắn chat" class="flex-grow bg-brand-surface border border-brand-border rounded-full px-4 py-2 text-sm focus:outline-none focus:border-brand-accent transition-colors" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button type="submit" aria-label="Gửi tin nhắn" class="w-9 h-9 rounded-full bg-brand-accent text-white flex items-center justify-center hover:bg-brand-accent-hover transition-colors flex-shrink-0 shadow-soft hover:shadow-hover">
                    <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Toggle Button -->
    <button id="toggle-chat" type="button" aria-label="Mở trợ lý ảo AI MNM" class="w-14 h-14 bg-brand-accent text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:-translate-y-1 hover:bg-brand-accent-hover transition-all duration-300 relative focus:outline-none">
        <svg id="chat-icon-open" class="w-7 h-7 absolute transition-all duration-300 transform scale-100 rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        <svg id="chat-icon-close" class="w-7 h-7 absolute transition-all duration-300 transform scale-0 rotate-90 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        
        <!-- Notification Dot -->
        <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('toggle-chat');
        const closeBtn = document.getElementById('close-chat');
        const chatWindow = document.getElementById('chat-window');
        const iconOpen = document.getElementById('chat-icon-open');
        const iconClose = document.getElementById('chat-icon-close');
        
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const chatMessages = document.getElementById('chat-messages');

        let isOpen = false;

        const toggleChat = () => {
            isOpen = !isOpen;
            if (isOpen) {
                chatWindow.classList.remove('hidden');
                setTimeout(() => {
                    chatWindow.classList.remove('scale-95', 'opacity-0');
                    chatWindow.classList.add('scale-100', 'opacity-100');
                }, 10);
                
                iconOpen.classList.replace('scale-100', 'scale-0');
                iconOpen.classList.replace('rotate-0', '-rotate-90');
                iconOpen.classList.add('opacity-0');
                
                iconClose.classList.replace('scale-0', 'scale-100');
                iconClose.classList.replace('rotate-90', 'rotate-0');
                iconClose.classList.remove('opacity-0');
                
                chatInput.focus();
            } else {
                chatWindow.classList.remove('scale-100', 'opacity-100');
                chatWindow.classList.add('scale-95', 'opacity-0');
                
                iconOpen.classList.replace('scale-0', 'scale-100');
                iconOpen.classList.replace('-rotate-90', 'rotate-0');
                iconOpen.classList.remove('opacity-0');
                
                iconClose.classList.replace('scale-100', 'scale-0');
                iconClose.classList.replace('rotate-0', 'rotate-90');
                iconClose.classList.add('opacity-0');
                
                setTimeout(() => {
                    chatWindow.classList.add('hidden');
                }, 300);
            }
        };

        toggleBtn.addEventListener('click', toggleChat);
        closeBtn.addEventListener('click', toggleChat);

        const escapeHtml = (str) => {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        const formatInlineText = (safeText) => {
            // Chuyển **text** thành in đậm gọn gàng, loại bỏ dấu * dư thừa
            return safeText
                .replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-brand-text">$1</strong>')
                .replace(/\*/g, '');
        };

        const formatChatMessage = (rawText, isUser) => {
            const safe = escapeHtml(String(rawText ?? ''));
            if (isUser) {
                return safe.replace(/\n/g, '<br>');
            }

            // Chuẩn hóa trường hợp AI viết nhiều mục "- **..." hoặc "- \"..." dính trên cùng một dòng
            const normalized = safe
                .replace(/\r\n/g, '\n')
                .replace(/\s+-\s+(?=\*\*|&quot;)/g, '\n- ');

            const lines = normalized.split('\n');
            const htmlParts = [];

            lines.forEach((rawLine) => {
                const line = rawLine.trim();
                if (!line) {
                    return;
                }

                // Bỏ dấu # tiêu đề markdown nếu có
                const cleanHeading = line.replace(/^#{1,6}\s+/, '');

                if (/^[-•*]\s+/.test(cleanHeading)) {
                    const itemText = formatInlineText(cleanHeading.replace(/^[-•*]\s+/, ''));
                    htmlParts.push(
                        `<div class="pl-3.5 relative my-1 leading-snug"><span class="absolute left-0 top-0 text-brand-accent font-bold">&bull;</span><span>${itemText}</span></div>`
                    );
                } else {
                    htmlParts.push(`<p class="my-1 leading-relaxed">${formatInlineText(cleanHeading)}</p>`);
                }
            });

            return htmlParts.join('') || formatInlineText(safe);
        };

        const appendMessage = (text, isUser = false) => {
            const wrapper = document.createElement('div');
            wrapper.className = `flex gap-2 w-full ${isUser ? 'justify-end' : 'justify-start'}`;

            const avatar = isUser ? '' : `
                <div class="w-7 h-7 rounded-full bg-brand-accent flex items-center justify-center flex-shrink-0 mt-auto mb-1">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            `;

            const bubbleStyle = isUser
                ? 'bg-brand-accent text-white rounded-2xl rounded-br-sm shadow-sm'
                : 'bg-white border border-brand-border text-brand-text rounded-2xl rounded-bl-sm shadow-sm';

            wrapper.innerHTML = `
                ${avatar}
                <div class="max-w-[85%] min-w-0 break-words px-3.5 py-2.5 ${bubbleStyle} leading-relaxed">
                    ${formatChatMessage(text, isUser)}
                </div>
            `;

            chatMessages.appendChild(wrapper);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const showTypingIndicator = () => {
            const wrapper = document.createElement('div');
            wrapper.id = 'typing-indicator';
            wrapper.className = `flex gap-2 w-max max-w-[85%]`;
            wrapper.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-brand-accent flex items-center justify-center flex-shrink-0 mt-auto mb-1">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="bg-white border border-brand-border px-3 py-3 rounded-2xl rounded-bl-sm flex gap-1 items-center shadow-sm h-[38px]">
                    <div class="w-1.5 h-1.5 bg-brand-muted rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-1.5 h-1.5 bg-brand-muted rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-1.5 h-1.5 bg-brand-muted rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            `;
            chatMessages.appendChild(wrapper);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const removeTypingIndicator = () => {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) indicator.remove();
        };

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            // User Message
            appendMessage(text, true);
            chatInput.value = '';

            // Simulate Bot Typing
            showTypingIndicator();
            
            // Call Laravel Backend
            fetch("{{ route('chat.respond') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: text })
            })
            .then(response => response.json())
            .then(data => {
                removeTypingIndicator();
                appendMessage(data.reply, false);
            })
            .catch(error => {
                removeTypingIndicator();
                appendMessage("Lỗi kết nối đến máy chủ. Vui lòng thử lại sau.", false);
            });
        });
    });
</script>

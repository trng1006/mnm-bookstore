<!-- Admin AI Assistant Floating Widget -->
<div id="admin-ai-widget" class="fixed bottom-5 right-5 z-50 flex flex-col items-end font-ui">
    <!-- Chat Window -->
    <div id="admin-ai-window" class="hidden w-[calc(100vw-2rem)] sm:w-[460px] bg-white rounded-2xl shadow-2xl border border-brand-border overflow-hidden mb-3 flex flex-col">
        <!-- Header -->
        <div class="bg-brand-text text-white px-4 py-3 flex items-center justify-between gap-2">
            <div>
                <p class="font-semibold text-sm">Trợ lý Quản trị AI</p>
                <p class="text-[11px] text-white/70">Truy vấn nhanh &bull; Thêm/Sửa/Xóa có xác nhận &bull; Có hoàn tác</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.ai.logs') }}" class="text-xs px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-white transition-colors">
                    Lịch sử
                </a>
                <button type="button" id="admin-ai-clear" class="text-xs px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer">
                    Xóa chat
                </button>
                <button type="button" id="admin-ai-close" aria-label="Đóng" class="text-white/80 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div class="px-3 py-2 bg-brand-surface border-b border-brand-border flex flex-wrap gap-1.5">
            <button type="button" class="admin-ai-chip text-[11px] px-2.5 py-1 rounded-full bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent cursor-pointer" data-prompt="Thống kê tổng quan doanh thu và đơn hàng hiện tại">
                Thống kê tổng quan
            </button>
            <button type="button" class="admin-ai-chip text-[11px] px-2.5 py-1 rounded-full bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent cursor-pointer" data-prompt="Liệt kê 5 bình luận chưa phản hồi mới nhất">
                Bình luận chưa trả lời
            </button>
            <button type="button" class="admin-ai-chip text-[11px] px-2.5 py-1 rounded-full bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent cursor-pointer" data-prompt="Tìm các cuốn sách tồn kho dưới 5 quyển">
                Sách sắp hết hàng
            </button>
        </div>

        <!-- Messages Area -->
        <div id="admin-ai-messages" class="h-[400px] max-h-[60vh] overflow-y-auto p-4 bg-brand-surface/70 flex flex-col gap-3 text-sm">
            <div class="bg-white border border-brand-border px-3.5 py-2.5 rounded-2xl rounded-bl-sm text-brand-text shadow-xs leading-relaxed max-w-[90%]">
                Xin chào {{ auth()->user()->name }}. Tôi có thể giúp bạn truy vấn số liệu, tìm kiếm hoặc chuẩn bị thao tác Thêm/Sửa/Xóa trên Sách, Danh mục, Tác giả, Mã giảm giá, Bình luận và Đơn hàng. Mọi lệnh thay đổi dữ liệu đều yêu cầu bạn bấm Xác nhận và có thể Hoàn tác nếu nhầm.
            </div>
        </div>

        <!-- Input Form -->
        <div class="p-3 bg-white border-t border-brand-border">
            <form id="admin-ai-form" class="flex gap-2 items-center">
                <input type="text" id="admin-ai-input" autocomplete="off"
                       placeholder="Ví dụ: Tìm bình luận 1 sao, hoặc Thêm mã giảm giá SALE15 giảm 15%..."
                       class="flex-1 bg-brand-surface border border-brand-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-brand-accent">
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-accent hover:bg-brand-accent-hover text-white text-sm font-semibold transition-colors cursor-pointer">
                    Gửi
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button type="button" id="admin-ai-toggle" aria-label="Mở Trợ lý AI Admin"
            class="px-4 h-12 rounded-full bg-brand-text hover:bg-brand-accent text-white font-semibold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        <span>Trợ lý AI Admin</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('admin-ai-toggle');
    const closeBtn = document.getElementById('admin-ai-close');
    const clearBtn = document.getElementById('admin-ai-clear');
    const chatWindow = document.getElementById('admin-ai-window');
    const messagesBox = document.getElementById('admin-ai-messages');
    const form = document.getElementById('admin-ai-form');
    const input = document.getElementById('admin-ai-input');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let lastSentMessage = '';

    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const scrollToBottom = () => {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    };

    const appendTextBubble = (text, isUser = false, isError = false) => {
        const wrapper = document.createElement('div');
        wrapper.className = `flex flex-col ${isUser ? 'items-end' : 'items-start'}`;

        const bubbleClass = isUser
            ? 'bg-brand-accent text-white rounded-2xl rounded-br-sm'
            : (isError
                ? 'bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl rounded-bl-sm'
                : 'bg-white border border-brand-border text-brand-text rounded-2xl rounded-bl-sm');

        const formatted = escapeHtml(text).replace(/\n/g, '<br>');
        wrapper.innerHTML = `<div class="max-w-[90%] px-3.5 py-2.5 text-sm shadow-xs leading-relaxed ${bubbleClass}">${formatted}</div>`;

        if (isError && lastSentMessage) {
            const retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.textContent = 'Thử lại';
            retryBtn.className = 'mt-1.5 text-xs font-semibold text-brand-accent hover:underline cursor-pointer';
            retryBtn.addEventListener('click', () => sendMessage(lastSentMessage));
            wrapper.appendChild(retryBtn);
        }

        messagesBox.appendChild(wrapper);
        scrollToBottom();
    };

    const appendPendingCard = (pending) => {
        const card = document.createElement('div');
        card.className = 'bg-white border-2 border-brand-accent/40 rounded-2xl p-3.5 shadow-sm max-w-[95%] space-y-2.5';

        let rowsHtml = '';
        Object.entries(pending.changes || {}).forEach(([label, val]) => {
            rowsHtml += `
                <div class="flex justify-between gap-3 text-xs border-b border-brand-border/50 py-1 last:border-none">
                    <span class="text-brand-muted font-medium">${escapeHtml(label)}:</span>
                    <span class="text-brand-text font-semibold text-right break-words">${escapeHtml(val)}</span>
                </div>
            `;
        });

        const warningHtml = pending.warning
            ? `<div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">${escapeHtml(pending.warning)}</div>`
            : '';

        card.innerHTML = `
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-accent">Yêu cầu xác nhận thao tác</span>
                <span class="text-[11px] px-2 py-0.5 rounded bg-brand-surface border border-brand-border font-mono">${escapeHtml(pending.action_type)}</span>
            </div>
            <div class="text-sm font-bold text-brand-text">${escapeHtml(pending.summary)}</div>
            <div class="bg-brand-surface/70 rounded-xl px-3 py-1.5">${rowsHtml}</div>
            ${warningHtml}
            <div class="flex items-center justify-end gap-2 pt-1 action-buttons">
                <button type="button" class="cancel-action-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-brand-border text-brand-muted hover:bg-brand-surface cursor-pointer">Hủy</button>
                <button type="button" class="confirm-action-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-brand-accent text-white hover:bg-brand-accent-hover cursor-pointer">Xác nhận thực thi</button>
            </div>
        `;

        const btnGroup = card.querySelector('.action-buttons');
        const confirmBtn = card.querySelector('.confirm-action-btn');
        const cancelBtn = card.querySelector('.cancel-action-btn');

        confirmBtn.addEventListener('click', () => {
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.textContent = 'Đang thực thi...';

            fetch("{{ route('admin.ai.confirm') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ action_id: pending.action_id })
            })
            .then(r => r.json())
            .then(data => {
                btnGroup.innerHTML = '';
                if (data.success) {
                    const statusEl = document.createElement('div');
                    statusEl.className = 'w-full flex items-center justify-between gap-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-2.5 py-2';
                    statusEl.innerHTML = `<span>${escapeHtml(data.message)}</span>`;

                    const undoBtn = document.createElement('button');
                    undoBtn.type = 'button';
                    undoBtn.textContent = 'Hoàn tác';
                    undoBtn.className = 'px-2.5 py-1 rounded bg-amber-600 text-white font-semibold hover:bg-amber-700 flex-shrink-0 cursor-pointer';
                    undoBtn.addEventListener('click', () => {
                        undoBtn.disabled = true;
                        undoBtn.textContent = 'Đang hoàn tác...';
                        fetch(data.undo_url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(res => res.json())
                        .then(undoRes => {
                            if (undoRes.success) {
                                statusEl.className = 'w-full text-xs text-brand-text bg-gray-100 border border-gray-200 rounded-lg px-2.5 py-2';
                                statusEl.textContent = undoRes.message;
                            } else {
                                undoBtn.disabled = false;
                                undoBtn.textContent = 'Hoàn tác';
                                appendTextBubble(undoRes.message || 'Không thể hoàn tác.', false, true);
                            }
                        })
                        .catch(() => {
                            undoBtn.disabled = false;
                            undoBtn.textContent = 'Hoàn tác';
                        });
                    });

                    statusEl.appendChild(undoBtn);
                    btnGroup.appendChild(statusEl);
                } else {
                    btnGroup.innerHTML = `<span class="text-xs text-red-600">${escapeHtml(data.message || 'Thao tác thất bại.')}</span>`;
                }
            })
            .catch(() => {
                btnGroup.innerHTML = '<span class="text-xs text-red-600">Lỗi kết nối khi xác nhận thao tác.</span>';
            });
        });

        cancelBtn.addEventListener('click', () => {
            fetch("{{ route('admin.ai.cancel') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ action_id: pending.action_id })
            }).finally(() => {
                btnGroup.innerHTML = '<span class="text-xs text-brand-muted italic">Đã hủy thao tác này.</span>';
            });
        });

        messagesBox.appendChild(card);
        scrollToBottom();
    };

    const showLoading = () => {
        const el = document.createElement('div');
        el.id = 'admin-ai-loading';
        el.className = 'text-xs text-brand-muted italic px-2';
        el.textContent = 'Đang xử lý yêu cầu...';
        messagesBox.appendChild(el);
        scrollToBottom();
    };

    const removeLoading = () => {
        document.getElementById('admin-ai-loading')?.remove();
    };

    const sendMessage = (text) => {
        const trimmed = text.trim();
        if (!trimmed) return;
        lastSentMessage = trimmed;

        appendTextBubble(trimmed, true);
        input.value = '';
        showLoading();

        fetch("{{ route('admin.ai.respond') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: trimmed })
        })
        .then(r => r.json())
        .then(data => {
            removeLoading();
            if (data.reply) {
                appendTextBubble(data.reply, false, Boolean(data.api_error));
            }
            if (data.pending_action) {
                appendPendingCard(data.pending_action);
            }
        })
        .catch(() => {
            removeLoading();
            appendTextBubble('Lỗi kết nối tới máy chủ Admin.', false, true);
        });
    };

    toggleBtn?.addEventListener('click', () => {
        chatWindow.classList.toggle('hidden');
        if (!chatWindow.classList.contains('hidden')) {
            input.focus();
        }
    });

    closeBtn?.addEventListener('click', () => chatWindow.classList.add('hidden'));

    clearBtn?.addEventListener('click', () => {
        fetch("{{ route('admin.ai.clear') }}", {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        }).then(() => {
            messagesBox.innerHTML = '<div class="bg-white border border-brand-border px-3.5 py-2.5 rounded-2xl rounded-bl-sm text-brand-text shadow-xs leading-relaxed max-w-[90%]">Đã làm mới đoạn hội thoại. Bạn cần tra cứu hay xử lý dữ liệu nào?</div>';
        });
    });

    document.querySelectorAll('.admin-ai-chip').forEach((chip) => {
        chip.addEventListener('click', () => sendMessage(chip.dataset.prompt || ''));
    });

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(input.value);
    });
});
</script>

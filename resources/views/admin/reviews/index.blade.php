@extends('admin.layouts.master')

@section('title', 'Quản lý Bình luận & Đánh giá')

@section('content')
<div class="space-y-6">
    @if($errors->has('admin_reply'))
        <div class="px-4 py-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm">
            {{ $errors->first('admin_reply') }}
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 pb-2 border-b border-brand-border">
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all' => ['label' => 'Tất cả', 'count' => $statusCounts['all']],
                'visible' => ['label' => 'Đang hiện', 'count' => $statusCounts['visible']],
                'hidden' => ['label' => 'Đã ẩn', 'count' => $statusCounts['hidden']],
                'unreplied' => ['label' => 'Chưa phản hồi', 'count' => $statusCounts['unreplied']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.reviews.index', array_merge(request()->except('status', 'page'), ['status' => $key])) }}"
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 {{ $currentStatus === $key ? 'bg-brand-accent text-white shadow-sm' : 'bg-white text-brand-muted hover:bg-brand-bg hover:text-brand-text border border-brand-border' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $currentStatus === $key ? 'bg-white/20 text-white' : 'bg-brand-surface text-brand-muted' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-brand-border shadow-sm">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="relative flex-1 w-full">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Tìm theo tên sách, tên khách hàng, email hoặc nội dung bình luận..."
                       class="w-full bg-brand-surface border border-brand-border rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-all">
                <svg class="w-4 h-4 text-brand-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <select name="rating" class="w-full sm:w-44 bg-brand-surface border border-brand-border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-brand-accent">
                <option value="">Tất cả mức sao</option>
                @for($star = 5; $star >= 1; $star--)
                    <option value="{{ $star }}" {{ (string) request('rating') === (string) $star ? 'selected' : '' }}>
                        {{ $star }} sao
                    </option>
                @endfor
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-brand-text text-white rounded-lg text-sm font-semibold hover:bg-brand-accent transition-colors cursor-pointer">
                Lọc dữ liệu
            </button>

            @if(request('search') || request('rating') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('admin.reviews.index') }}" class="text-xs text-brand-muted hover:text-brand-accent underline whitespace-nowrap">
                    Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-brand-surface text-brand-muted text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Sản phẩm</th>
                        <th class="py-3.5 px-4">Độc giả</th>
                        <th class="py-3.5 px-4">Đánh giá & Bình luận</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4">Thời gian</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-brand-surface/40 transition-colors align-top">
                            <td class="py-4 px-4 w-56">
                                @if($review->book)
                                    <a href="{{ route('books.show', $review->book->slug) }}" target="_blank" class="flex items-start gap-2.5 group">
                                        @if($review->book->cover_image)
                                            <img src="{{ $review->book->cover_image }}" alt="{{ $review->book->title }}" class="w-10 h-14 object-cover rounded border border-brand-border flex-shrink-0">
                                        @endif
                                        <div>
                                            <div class="font-semibold text-brand-text group-hover:text-brand-accent line-clamp-2">{{ $review->book->title }}</div>
                                            <div class="text-[11px] text-brand-muted mt-0.5">ID sách: #{{ $review->book->id }}</div>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-brand-muted">Sách đã bị xóa</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 w-44">
                                <div class="font-semibold text-brand-text">{{ $review->user->name ?? 'Người dùng đã xóa' }}</div>
                                <div class="text-xs text-brand-muted break-all">{{ $review->user->email ?? '' }}</div>
                            </td>

                            <td class="py-4 px-4 min-w-[280px]">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <div class="flex text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-bold text-brand-text">{{ $review->rating }}/5</span>
                                </div>

                                <p class="text-sm text-brand-text leading-relaxed">{{ $review->comment }}</p>

                                @if(!empty($review->admin_reply))
                                    <div class="mt-2.5 p-2.5 bg-brand-surface border-l-4 border-brand-accent rounded-r-lg">
                                        <div class="flex items-center justify-between gap-2 text-[11px] mb-0.5">
                                            <span class="font-bold text-brand-accent">
                                                Phản hồi của cửa hàng
                                                @if($review->repliedBy)
                                                    ({{ $review->repliedBy->name }})
                                                @endif
                                            </span>
                                            <form action="{{ route('admin.reviews.deleteReply', $review) }}" method="POST" onsubmit="return confirm('Xóa phản hồi của cửa hàng cho bình luận này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline cursor-pointer">Xóa phản hồi</button>
                                            </form>
                                        </div>
                                        <p class="text-xs text-brand-text">{{ $review->admin_reply }}</p>
                                    </div>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($review->is_visible)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Đang hiện
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                        Đã ẩn
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-xs text-brand-muted whitespace-nowrap">
                                <div>{{ $review->created_at->format('d/m/Y H:i') }}</div>
                                @if($review->replied_at)
                                    <div class="text-[11px] mt-1">Trả lời: {{ $review->replied_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-right whitespace-nowrap space-y-1.5">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            class="open-reply-modal-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-brand-text text-white hover:bg-brand-accent transition-colors cursor-pointer"
                                            data-action="{{ route('admin.reviews.reply', $review) }}"
                                            data-book-title="{{ $review->book->title ?? 'Sản phẩm' }}"
                                            data-user-name="{{ $review->user->name ?? 'Khách hàng' }}"
                                            data-comment="{{ $review->comment }}"
                                            data-reply="{{ $review->admin_reply ?? '' }}">
                                        {{ !empty($review->admin_reply) ? 'Sửa phản hồi' : 'Phản hồi' }}
                                    </button>

                                    <form action="{{ route('admin.reviews.toggleVisibility', $review) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-colors cursor-pointer {{ $review->is_visible ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                            {{ $review->is_visible ? 'Ẩn' : 'Hiện' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bình luận này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors cursor-pointer">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-brand-muted">
                                Không tìm thấy bình luận nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-brand-border">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<!-- Modal Phản hồi Bình luận -->
<div id="admin-reply-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-border flex items-center justify-between bg-brand-surface">
            <h3 class="font-semibold text-base text-brand-text">Phản hồi bình luận khách hàng</h3>
            <button type="button" id="close-reply-modal" class="text-brand-muted hover:text-brand-text cursor-pointer" aria-label="Đóng">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="admin-reply-form" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div class="p-3 bg-brand-surface rounded-xl border border-brand-border/70 text-xs space-y-1">
                <div class="font-semibold text-brand-text" id="modal-review-meta"></div>
                <p class="text-brand-muted italic" id="modal-review-comment"></p>
            </div>

            <div>
                <label for="modal-admin-reply-input" class="block text-sm font-semibold text-brand-text mb-1.5">Nội dung phản hồi của cửa hàng</label>
                <textarea id="modal-admin-reply-input" name="admin_reply" rows="4" required maxlength="1000"
                          placeholder="Nhập lời cảm ơn hoặc giải đáp gửi tới độc giả..."
                          class="w-full bg-white border border-brand-border rounded-xl p-3 text-sm focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" id="cancel-reply-modal" class="px-4 py-2 rounded-xl text-sm font-medium border border-brand-border text-brand-text hover:bg-brand-surface cursor-pointer">
                    Hủy
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-brand-accent text-white hover:bg-brand-accent-hover cursor-pointer">
                    Lưu phản hồi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('admin-reply-modal');
    const form = document.getElementById('admin-reply-form');
    const metaEl = document.getElementById('modal-review-meta');
    const commentEl = document.getElementById('modal-review-comment');
    const replyInput = document.getElementById('modal-admin-reply-input');
    const closeBtn = document.getElementById('close-reply-modal');
    const cancelBtn = document.getElementById('cancel-reply-modal');

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('.open-reply-modal-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.action || '';
            metaEl.textContent = (btn.dataset.userName || '') + ' — ' + (btn.dataset.bookTitle || '');
            commentEl.textContent = '"' + (btn.dataset.comment || '') + '"';
            replyInput.value = btn.dataset.reply || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            replyInput.focus();
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
});
</script>
@endpush

@extends('admin.layouts.master')

@section('title', 'Lịch sử thao tác Trợ lý AI')

@section('content')
<div class="space-y-6">
    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-brand-border shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.ai.logs') }}" class="flex flex-wrap items-center gap-3 w-full">
            <select name="status" class="bg-brand-surface border border-brand-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-accent">
                <option value="">Tất cả trạng thái</option>
                <option value="executed" {{ request('status') === 'executed' ? 'selected' : '' }}>Đã thực thi</option>
                <option value="undone" {{ request('status') === 'undone' ? 'selected' : '' }}>Đã hoàn tác</option>
            </select>

            <select name="target_type" class="bg-brand-surface border border-brand-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-accent">
                <option value="">Tất cả đối tượng</option>
                <option value="book" {{ request('target_type') === 'book' ? 'selected' : '' }}>Sách</option>
                <option value="category" {{ request('target_type') === 'category' ? 'selected' : '' }}>Danh mục</option>
                <option value="author" {{ request('target_type') === 'author' ? 'selected' : '' }}>Tác giả</option>
                <option value="coupon" {{ request('target_type') === 'coupon' ? 'selected' : '' }}>Mã giảm giá</option>
                <option value="review" {{ request('target_type') === 'review' ? 'selected' : '' }}>Bình luận</option>
                <option value="order" {{ request('target_type') === 'order' ? 'selected' : '' }}>Đơn hàng</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-brand-text text-white rounded-lg text-sm font-semibold hover:bg-brand-accent transition-colors cursor-pointer">
                Lọc
            </button>

            @if(request('status') || request('target_type'))
                <a href="{{ route('admin.ai.logs') }}" class="text-xs text-brand-muted hover:text-brand-accent underline">
                    Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-brand-surface text-brand-muted text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">ID</th>
                        <th class="py-3.5 px-4">Thời gian</th>
                        <th class="py-3.5 px-4">Người xác nhận</th>
                        <th class="py-3.5 px-4">Thao tác</th>
                        <th class="py-3.5 px-4">Nội dung</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-right">Khôi phục</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($logs as $log)
                        <tr class="hover:bg-brand-surface/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs text-brand-muted">#{{ $log->id }}</td>
                            <td class="py-3.5 px-4 text-xs text-brand-muted whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-medium text-brand-text">
                                {{ $log->user->name ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold border bg-brand-surface text-brand-text border-brand-border uppercase">
                                    {{ $log->action_type }} / {{ $log->target_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-brand-text">
                                {{ $log->summary }}
                                @if($log->status === 'undone' && $log->undone_at)
                                    <div class="text-xs text-brand-muted mt-0.5">
                                        Đã hoàn tác bởi {{ $log->undoneBy->name ?? 'N/A' }} lúc {{ $log->undone_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($log->status === 'executed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Đã thực thi
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                        Đã hoàn tác
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            class="view-snapshot-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-brand-border bg-white text-brand-text hover:bg-brand-surface cursor-pointer"
                                            data-summary="{{ $log->summary }}"
                                            data-before="{{ json_encode($log->before_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}"
                                            data-after="{{ json_encode($log->after_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}">
                                        Snapshot
                                    </button>

                                    @if($log->status === 'executed')
                                        <form action="{{ route('admin.ai.undo', $log) }}" method="POST" onsubmit="return confirm('Hoàn tác thao tác #{{ $log->id }} và khôi phục dữ liệu trước đó?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 text-white hover:bg-amber-700 cursor-pointer">
                                                Hoàn tác
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-brand-muted">
                                Chưa có thao tác ghi dữ liệu nào từ Trợ lý AI Admin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-brand-border">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Snapshot Detail Modal -->
<div id="ai-snapshot-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xl w-full max-w-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-border flex items-center justify-between bg-brand-surface">
            <h3 class="font-semibold text-base text-brand-text" id="snapshot-modal-title">Chi tiết Snapshot Backup</h3>
            <button type="button" id="close-snapshot-modal" class="text-brand-muted hover:text-brand-text cursor-pointer" aria-label="Đóng">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
            <div>
                <div class="text-xs font-bold uppercase text-brand-muted mb-1.5">Dữ liệu trước (Before)</div>
                <pre id="snapshot-before" class="text-xs bg-brand-surface border border-brand-border rounded-xl p-3 overflow-x-auto whitespace-pre-wrap"></pre>
            </div>
            <div>
                <div class="text-xs font-bold uppercase text-brand-muted mb-1.5">Dữ liệu sau (After)</div>
                <pre id="snapshot-after" class="text-xs bg-brand-surface border border-brand-border rounded-xl p-3 overflow-x-auto whitespace-pre-wrap"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('ai-snapshot-modal');
    const titleEl = document.getElementById('snapshot-modal-title');
    const beforeEl = document.getElementById('snapshot-before');
    const afterEl = document.getElementById('snapshot-after');
    const closeBtn = document.getElementById('close-snapshot-modal');

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('.view-snapshot-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            titleEl.textContent = btn.dataset.summary || 'Chi tiết Snapshot Backup';
            beforeEl.textContent = btn.dataset.before && btn.dataset.before !== 'null' ? btn.dataset.before : '(Không có)';
            afterEl.textContent = btn.dataset.after && btn.dataset.after !== 'null' ? btn.dataset.after : '(Không có)';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
});
</script>
@endpush

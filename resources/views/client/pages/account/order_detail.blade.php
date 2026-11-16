@extends('client.layouts.master')

@section('title', 'Chi tiết đơn hàng #' . $order->order_number . ' - MNM Bookstore')

@section('content')
<div class="py-8 max-w-5xl mx-auto">
    <!-- Top bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('account.profile') }}" class="text-brand-muted hover:text-brand-accent transition-colors p-1" title="Quay lại">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text">
                    Đơn hàng #{{ $order->order_number }}
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                    {{ $order->status_label }}
                </span>
            </div>
            <p class="font-ui text-xs text-brand-muted mt-1 ml-8">
                Đặt vào lúc {{ $order->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        @if($order->status === \App\Models\Order::STATUS_PENDING)
            <form method="POST" action="{{ route('account.orders.cancel', $order->order_number) }}">
                @csrf
                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')" class="font-ui text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl px-4 py-2 transition-colors cursor-pointer">
                    Hủy đơn hàng này
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Status Timeline / Stepper -->
    @if($order->status !== \App\Models\Order::STATUS_CANCELLED && $order->status !== \App\Models\Order::STATUS_PAYMENT_FAILED)
        @php
            $steps = [
                'pending' => ['label' => 'Chờ xác nhận', 'icon' => '1'],
                'confirmed' => ['label' => 'Đã xác nhận', 'icon' => '2'],
                'packing' => ['label' => 'Đang đóng gói', 'icon' => '3'],
                'shipping' => ['label' => 'Đang giao hàng', 'icon' => '4'],
                'completed' => ['label' => 'Hoàn thành', 'icon' => '✓'],
            ];
            $orderStatusWeights = [
                'pending' => 1,
                'confirmed' => 2,
                'packing' => 3,
                'shipping' => 4,
                'completed' => 5,
            ];
            $currentWeight = $orderStatusWeights[$order->status] ?? 1;
        @endphp

        <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm mb-8 font-ui">
            <h3 class="text-xs uppercase tracking-wider text-brand-muted font-bold mb-6">Tiến trình vận chuyển</h3>
            <div class="relative max-w-4xl mx-auto">
                <!-- Line aligned to dead-center of 40px circles (top-5 = 20px) -->
                <div class="absolute left-[10%] right-[10%] top-5 -translate-y-1/2 h-1 bg-brand-surface z-0">
                    <div class="h-full bg-brand-accent transition-all duration-700" style="width: {{ (($currentWeight - 1) / 4) * 100 }}%"></div>
                </div>

                <!-- 5 Steps in Equal Columns -->
                <div class="relative z-10 grid grid-cols-5">
                    @foreach($steps as $key => $step)
                        @php
                            $stepWeight = $orderStatusWeights[$key];
                            $isPastOrCurrent = $currentWeight >= $stepWeight;
                            $isCurrent = $currentWeight === $stepWeight;
                        @endphp
                        <div class="flex flex-col items-center text-center px-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 {{ $isPastOrCurrent ? 'bg-brand-accent text-white shadow-soft' : 'bg-brand-surface border border-brand-border text-brand-muted' }} {{ $isCurrent ? 'ring-4 ring-brand-accent/20 scale-105' : '' }}">
                                {{ $step['icon'] }}
                            </div>
                            <span class="text-xs font-semibold mt-2.5 leading-tight {{ $isCurrent ? 'text-brand-accent' : ($isPastOrCurrent ? 'text-brand-text' : 'text-brand-muted') }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="p-6 bg-red-50 border border-red-200 rounded-[var(--radius-card)] mb-8 font-ui flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-lg flex-shrink-0">✕</div>
            <div>
                <h4 class="font-bold text-red-800 text-sm">Đơn hàng này đã bị hủy</h4>
                <p class="text-xs text-red-600 mt-0.5">Số lượng sách đã được hoàn trả về kho. Nếu có thắc mắc, vui lòng liên hệ bộ phận chăm sóc khách hàng.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start font-ui">
        <!-- Left: Order Items (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h3 class="font-display text-lg font-bold text-brand-text mb-4 pb-3 border-b border-brand-border">
                Danh sách sách đã mua
            </h3>

            <div class="divide-y divide-brand-border/60">
                @foreach($order->items as $item)
                    @php
                        $book = $item->book;
                        $review = ($book && isset($userReviews[$book->id])) ? $userReviews[$book->id] : null;
                        $isOrderCompleted = ($order->status === \App\Models\Order::STATUS_COMPLETED);
                    @endphp
                    <div class="py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <div class="w-14 h-20 bg-brand-surface rounded-lg overflow-hidden flex-shrink-0 border border-brand-border flex items-center justify-center">
                                    @if(!empty($item->book_cover))
                                        <img src="{{ $item->book_cover }}" alt="{{ $item->book_title }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-brand-muted">Bìa</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if($book)
                                        <a href="{{ route('books.show', $book->slug) }}" class="font-display font-bold text-sm text-brand-text hover:text-brand-accent transition-colors line-clamp-2" title="{{ $item->book_title }}">
                                            {{ $item->book_title }}
                                        </a>
                                    @else
                                        <h4 class="font-display font-bold text-sm text-brand-text line-clamp-2">{{ $item->book_title }}</h4>
                                    @endif
                                    <div class="text-xs text-brand-muted mt-1">
                                        {{ number_format($item->price, 0, ',', '.') }} ₫ × {{ $item->quantity }}
                                    </div>
                                </div>
                            </div>

                            <div class="font-display font-bold text-sm text-brand-accent whitespace-nowrap pt-0.5">
                                {{ number_format($item->total, 0, ',', '.') }} ₫
                            </div>
                        </div>

                        <!-- Khu vực đánh giá cho từng quyển sách đã mua -->
                        @if($isOrderCompleted && $book)
                            <div class="mt-3.5 pl-18 flex flex-wrap items-center justify-between gap-2 bg-brand-surface/60 p-2.5 rounded-xl border border-brand-border/50">
                                @if($review)
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <div class="flex text-yellow-400 text-xs">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                        <span class="text-xs font-semibold text-brand-text">{{ $review->rating }}/5</span>
                                        <span class="text-xs text-brand-muted font-ui line-clamp-1 italic max-w-xs">“{{ $review->comment }}”</span>
                                    </div>
                                    @if($review->is_visible)
                                        <button type="button" 
                                                onclick="openReviewModal('{{ $book->id }}', '{{ addslashes($book->title) }}', '{{ $item->book_cover ?? '' }}', '{{ $book->slug }}', {{ $review->rating }}, '{{ addslashes($review->comment) }}')"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-brand-accent hover:text-brand-accent-hover transition-colors cursor-pointer px-2.5 py-1 rounded-lg hover:bg-white/80">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Sửa đánh giá
                                        </button>
                                    @else
                                        <span class="text-[11px] font-semibold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-lg">
                                            Đánh giá đang bị quản trị viên ẩn
                                        </span>
                                    @endif
                                @else
                                    <div class="text-xs text-brand-muted font-ui">
                                        Bạn thấy cuốn sách này như thế nào?
                                    </div>
                                    <button type="button" 
                                            onclick="openReviewModal('{{ $book->id }}', '{{ addslashes($book->title) }}', '{{ $item->book_cover ?? '' }}', '{{ $book->slug }}', 5, '')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold font-ui text-white bg-brand-accent hover:bg-brand-accent-hover transition-all shadow-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 fill-current text-white" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Đánh giá sản phẩm
                                    </button>
                                @endif
                            </div>
                        @elseif(!$isOrderCompleted)
                            <div class="mt-2 pl-18 text-[11px] text-brand-muted font-ui italic">
                                * Có thể đánh giá sau khi nhận hàng thành công
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Financials -->
            <div class="border-t border-brand-border pt-4 mt-4 space-y-2.5 text-sm text-brand-muted">
                <div class="flex justify-between">
                    <span>Tạm tính</span>
                    <span class="font-semibold text-brand-text">{{ number_format($order->subtotal, 0, ',', '.') }} ₫</span>
                </div>
                <div class="flex justify-between">
                    <span>Phí vận chuyển</span>
                    <span class="font-semibold text-brand-text">{{ $order->shipping_fee == 0 ? 'Miễn phí' : number_format($order->shipping_fee, 0, ',', '.') . ' ₫' }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-brand-accent">
                        <span>Giảm giá @if($order->coupon_code) (Mã: {{ $order->coupon_code }}) @endif</span>
                        <span class="font-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
                    </div>
                @endif
                <div class="pt-3 border-t border-brand-border flex justify-between items-baseline">
                    <span class="font-bold text-base text-brand-text">Tổng tiền</span>
                    <span class="font-display font-bold text-2xl text-brand-accent">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
                </div>
            </div>
        </div>

        <!-- Right: Shipping & Payment Info (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Delivery Info -->
            <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-brand-text mb-4 pb-3 border-b border-brand-border flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Địa chỉ nhận hàng
                </h3>
                <div class="text-sm space-y-2 text-brand-text">
                    <div class="font-bold">{{ $order->customer_name }}</div>
                    <div class="text-xs text-brand-muted">SĐT: {{ $order->customer_phone }}</div>
                    <div class="text-xs text-brand-muted">Email: {{ $order->customer_email }}</div>
                    <div class="text-xs text-brand-text pt-2 leading-relaxed">{{ $order->shipping_address }}</div>
                    @if($order->shipping_notes)
                        <div class="mt-2 p-2.5 bg-brand-surface rounded-lg text-xs text-brand-muted border border-brand-border/60">
                            <strong>Ghi chú:</strong> {{ $order->shipping_notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-brand-text mb-4 pb-3 border-b border-brand-border flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Phương thức thanh toán
                </h3>
                <div class="text-sm space-y-2">
                    <div class="font-semibold text-brand-text">{{ $order->payment_method_label }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-brand-muted">Trạng thái:</span>
                        <span class="px-2 py-0.5 rounded-full font-semibold border {{ $order->payment_status_badge_class }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </div>
                    @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid' && in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PAYMENT_FAILED]) && $order->total_amount > 0)
                        <div class="pt-3">
                            <a href="{{ route('checkout.payos.pay', $order->order_number) }}" class="inline-flex items-center justify-center w-full font-bold text-xs text-white bg-brand-accent hover:bg-brand-accent-hover px-4 py-2.5 rounded-xl transition-colors shadow-soft">
                                Thanh toán ngay qua QR (payOS)
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Đánh Giá Sách Từng Quyển -->
<div id="review-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop mờ -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" onclick="closeReviewModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-brand-border p-6 font-ui">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-brand-border">
                <div class="flex items-center gap-2">
                    <span class="text-yellow-400 text-lg">★</span>
                    <h3 class="font-display text-lg font-bold text-brand-text" id="modal-title">
                        Đánh giá sản phẩm
                    </h3>
                </div>
                <button type="button" onclick="closeReviewModal()" class="text-brand-muted hover:text-brand-text p-1.5 rounded-lg hover:bg-brand-surface transition-colors cursor-pointer" aria-label="Đóng">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Preview sách được đánh giá -->
            <div class="flex items-center gap-3.5 my-5 p-3.5 bg-brand-surface rounded-xl border border-brand-border/60">
                <div class="w-12 h-16 rounded-lg overflow-hidden bg-white flex-shrink-0 border border-brand-border flex items-center justify-center">
                    <img id="modal-book-cover" src="" alt="" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                    <h4 id="modal-book-title" class="font-display font-bold text-sm text-brand-text line-clamp-2"></h4>
                    <p class="text-xs text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Đã mua trong đơn #{{ $order->order_number }}
                    </p>
                </div>
            </div>

            <!-- Form đánh giá -->
            <form id="modal-review-form" method="POST" action="">
                @csrf
                
                <!-- Chọn số sao (Interactive Star Picker) -->
                <div class="mb-5 text-center">
                    <label class="block text-xs font-semibold text-brand-muted uppercase tracking-wider mb-2">Chất lượng sản phẩm:</label>
                    <div class="flex justify-center items-center gap-1.5" id="star-picker-container">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" data-star="{{ $i }}" class="star-btn p-1 text-gray-300 hover:scale-115 transition-transform text-3xl focus:outline-none cursor-pointer" aria-label="{{ $i }} sao">
                                ★
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="modal-rating-input" value="5">
                    <p id="star-label-text" class="text-xs text-brand-accent mt-2 font-bold font-ui">Cực kỳ hài lòng (5/5)</p>
                </div>

                <!-- Textarea nhận xét -->
                <div class="mb-5">
                    <label for="modal-comment" class="block text-xs font-semibold text-brand-text mb-1.5">Chia sẻ cảm nhận chi tiết:</label>
                    <textarea id="modal-comment" name="comment" rows="4" required minlength="5" placeholder="Cuốn sách có đúng kỳ vọng của bạn không? Nội dung và chất lượng in ấn như thế nào..." class="w-full bg-brand-surface border border-brand-border rounded-xl p-3 text-sm focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-colors"></textarea>
                    <p class="text-[11px] text-brand-muted mt-1 italic">* Tối thiểu 5 ký tự để giúp cộng đồng độc giả có thêm thông tin tham khảo hữu ích.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-border">
                    <button type="button" onclick="closeReviewModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-brand-muted hover:bg-brand-surface transition-colors cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-accent hover:bg-brand-accent-hover transition-colors shadow-soft cursor-pointer">
                        Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const ratingLabels = {
        1: 'Rất không hài lòng (1/5)',
        2: 'Không hài lòng (2/5)',
        3: 'Bình thường (3/5)',
        4: 'Hài lòng (4/5)',
        5: 'Cực kỳ hài lòng (5/5)'
    };

    function setStarRating(rating) {
        const input = document.getElementById('modal-rating-input');
        const label = document.getElementById('star-label-text');
        input.value = rating;
        label.textContent = ratingLabels[rating] || (rating + '/5');

        const buttons = document.querySelectorAll('#star-picker-container .star-btn');
        buttons.forEach((btn) => {
            const star = parseInt(btn.getAttribute('data-star'));
            if (star <= rating) {
                btn.classList.remove('text-gray-300');
                btn.classList.add('text-yellow-400');
            } else {
                btn.classList.remove('text-yellow-400');
                btn.classList.add('text-gray-300');
            }
        });
    }

    document.querySelectorAll('#star-picker-container .star-btn').forEach((btn) => {
        btn.addEventListener('click', function() {
            const star = parseInt(this.getAttribute('data-star'));
            setStarRating(star);
        });
        btn.addEventListener('mouseenter', function() {
            const hoverStar = parseInt(this.getAttribute('data-star'));
            const buttons = document.querySelectorAll('#star-picker-container .star-btn');
            buttons.forEach((b) => {
                const s = parseInt(b.getAttribute('data-star'));
                if (s <= hoverStar) {
                    b.classList.add('text-yellow-400');
                    b.classList.remove('text-gray-300');
                } else {
                    b.classList.remove('text-yellow-400');
                    b.classList.add('text-gray-300');
                }
            });
            const label = document.getElementById('star-label-text');
            label.textContent = ratingLabels[hoverStar] || '';
        });
    });

    document.getElementById('star-picker-container')?.addEventListener('mouseleave', function() {
        const input = document.getElementById('modal-rating-input');
        setStarRating(parseInt(input.value) || 5);
    });

    function openReviewModal(bookId, bookTitle, bookCover, bookSlug, rating, comment) {
        const modal = document.getElementById('review-modal');
        const form = document.getElementById('modal-review-form');
        const titleEl = document.getElementById('modal-book-title');
        const coverEl = document.getElementById('modal-book-cover');
        const commentEl = document.getElementById('modal-comment');

        titleEl.textContent = bookTitle;
        coverEl.src = bookCover || 'https://placehold.co/100x150?text=Bìa';
        commentEl.value = comment || '';

        form.action = '/books/' + bookSlug + '/reviews';
        setStarRating(rating || 5);

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeReviewModal() {
        const modal = document.getElementById('review-modal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReviewModal();
        }
    });
</script>
@endsection


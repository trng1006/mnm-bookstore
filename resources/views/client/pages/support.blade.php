@extends('client.layouts.master')

@section('title', 'Trung tâm hỗ trợ - MNM Bookstore')

@section('content')
<div class="py-10 max-w-5xl mx-auto font-ui">
    <!-- Breadcrumb & Header -->
    <nav aria-label="Breadcrumb" class="text-xs text-brand-muted mb-4 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-brand-accent transition-colors">Trang chủ</a>
        <span>/</span>
        <span class="text-brand-text font-medium">Trung tâm hỗ trợ</span>
    </nav>

    <div class="mb-8 pb-6 border-b border-brand-border">
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-brand-accent mb-2">Hỗ trợ độc giả</span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-brand-text tracking-tight mb-3">
            Vận chuyển, Đổi trả & Giải đáp thắc mắc
        </h1>
        <p class="text-brand-muted text-sm md:text-base max-w-2xl leading-relaxed">
            Thông tin chi tiết về quy trình giao nhận, điều kiện đổi trả, phương thức thanh toán và các câu hỏi thường gặp khi mua sách tại MNM Bookstore.
        </p>

        <!-- Quick Anchor Navigation -->
        <div class="mt-6 flex flex-wrap gap-2.5">
            <a href="#van-chuyen" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors">Vận chuyển & Giao nhận</a>
            <a href="#doi-tra" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors">Đổi trả & Hoàn tiền</a>
            <a href="#thanh-toan" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors">Phương thức thanh toán</a>
            <a href="#faq" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors">Câu hỏi thường gặp</a>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Section 1: Vận chuyển & Giao nhận -->
        <section id="van-chuyen" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Vận chuyển & Giao nhận</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border/70">
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-1">Phí giao hàng chuẩn</div>
                    <div class="font-display text-xl font-bold text-brand-text">30.000 ₫</div>
                    <p class="text-xs text-brand-muted mt-1">Áp dụng cho đơn hàng dưới 300.000 ₫ trên toàn quốc.</p>
                </div>
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border/70">
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-1">Miễn phí vận chuyển</div>
                    <div class="font-display text-xl font-bold text-brand-accent">Từ 300.000 ₫</div>
                    <p class="text-xs text-brand-muted mt-1">Tự động giảm phí ship về 0 ₫ tại giỏ hàng và thanh toán.</p>
                </div>
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border/70">
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-1">Thời gian dự kiến</div>
                    <div class="font-display text-xl font-bold text-brand-text">1 - 4 ngày</div>
                    <p class="text-xs text-brand-muted mt-1">Nội thành TP.HCM 1-2 ngày; các tỉnh thành khác 2-4 ngày làm việc.</p>
                </div>
            </div>
            <p class="text-sm text-brand-text/90 leading-relaxed">
                Tất cả sách đều được bọc chống sốc cẩn thận trước khi bàn giao đơn vị vận chuyển. Bạn có thể theo dõi trạng thái xử lý bất kỳ lúc nào qua trang <a href="{{ route('orders.tracking') }}" class="text-brand-accent underline font-medium">Tra cứu đơn hàng</a> bằng mã đơn và số điện thoại.
            </p>
        </section>

        <!-- Section 2: Đổi trả & Hoàn tiền -->
        <section id="doi-tra" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Đổi trả & Hoàn tiền</h2>
            <ul class="space-y-3 text-sm text-brand-text/90 leading-relaxed list-disc pl-5">
                <li><strong>Thời hạn tiếp nhận:</strong> Hỗ trợ đổi mới hoặc hoàn tiền trong vòng <strong>7 ngày</strong> kể từ ngày giao hàng thành công.</li>
                <li><strong>Trường hợp áp dụng:</strong> Sách bị lỗi in ấn (thiếu trang, mờ chữ, rách gáy), giao nhầm tựa sách hoặc hư hỏng trong quá trình vận chuyển.</li>
                <li><strong>Điều kiện sách đổi trả:</strong> Sách còn nguyên vẹn, chưa qua sử dụng, không bị viết vẽ hay rách bìa do tác động từ phía người nhận.</li>
                <li><strong>Hoàn tiền đơn thanh toán chuyển khoản:</strong> Khi đơn ở trạng thái Chờ xác nhận và được hủy hợp lệ, hệ thống tự động hoàn tồn kho; các giao dịch đã thanh toán qua VietQR sẽ được đối soát hoàn tiền về tài khoản nguồn trong 1-3 ngày làm việc.</li>
            </ul>
        </section>

        <!-- Section 3: Phương thức thanh toán -->
        <section id="thanh-toan" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Phương thức thanh toán</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="p-5 rounded-xl bg-brand-surface border border-brand-border/70">
                    <h3 class="font-display text-lg font-bold text-brand-text mb-2">Thanh toán khi nhận hàng (COD)</h3>
                    <p class="text-sm text-brand-muted leading-relaxed">
                        Áp dụng cho khách hàng đã đăng nhập tài khoản thành viên. Bạn thanh toán tiền mặt trực tiếp cho nhân viên giao nhận sau khi kiểm tra kiện hàng.
                    </p>
                </div>
                <div class="p-5 rounded-xl bg-brand-surface border border-brand-border/70">
                    <h3 class="font-display text-lg font-bold text-brand-text mb-2">Chuyển khoản VietQR / payOS</h3>
                    <p class="text-sm text-brand-muted leading-relaxed">
                        Hỗ trợ cả khách vãng lai lẫn thành viên. Hệ thống tạo mã VietQR tự động điền sẵn số tiền và nội dung chuyển khoản, xác nhận thanh toán ngay lập tức qua webhook bảo mật.
                    </p>
                </div>
            </div>
        </section>

        <!-- Section 4: Câu hỏi thường gặp (FAQ) -->
        <section id="faq" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Câu hỏi thường gặp</h2>
            @php
                $faqs = [
                    [
                        'q' => 'Tôi không có tài khoản có đặt mua sách được không?',
                        'a' => 'Có. Khách vãng lai có thể thêm sách vào giỏ và đặt hàng trực tiếp bằng phương thức chuyển khoản VietQR / payOS, sau đó tra cứu lại đơn hàng bằng mã đơn và số điện thoại.'
                    ],
                    [
                        'q' => 'Khi nào tôi có thể tự hủy đơn hàng đã đặt?',
                        'a' => 'Khi đơn hàng đang ở trạng thái Chờ xác nhận, bạn có thể vào mục Hồ sơ cá nhân > Chi tiết đơn hàng để bấm Hủy đơn. Tồn kho sách và lượt dùng mã giảm giá sẽ được hoàn lại tự động.'
                    ],
                    [
                        'q' => 'Làm sao để gửi đánh giá và chấm sao cho một cuốn sách?',
                        'a' => 'Để đảm bảo tính xác thực, chỉ những tài khoản đã đặt mua cuốn sách đó và đơn hàng đã chuyển sang trạng thái Hoàn thành mới có quyền gửi đánh giá tại trang chi tiết sách.'
                    ],
                    [
                        'q' => 'Mã giảm giá có áp dụng đồng thời với miễn phí vận chuyển không?',
                        'a' => 'Có. Phí vận chuyển được tính dựa trên tạm tính giỏ hàng từ 300.000 ₫ trở lên, và bạn vẫn có thể áp dụng thêm một mã giảm giá hợp lệ.'
                    ],
                ];
            @endphp

            <div class="divide-y divide-brand-border border-t border-b border-brand-border">
                @foreach($faqs as $idx => $faq)
                <div class="py-4">
                    <button type="button" class="faq-toggle w-full flex items-center justify-between gap-4 text-left font-semibold text-brand-text hover:text-brand-accent transition-colors cursor-pointer" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="faq-answer-{{ $idx }}">
                        <span>{{ $faq['q'] }}</span>
                        <span class="faq-indicator text-xs font-mono text-brand-muted flex-shrink-0">{{ $idx === 0 ? '[-]' : '[+]' }}</span>
                    </button>
                    <div id="faq-answer-{{ $idx }}" class="faq-panel mt-2.5 text-sm text-brand-muted leading-relaxed {{ $idx === 0 ? '' : 'hidden' }}">
                        {{ $faq['a'] }}
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 pt-2">
                <span class="text-sm text-brand-muted">Cần kiểm tra tiến độ giao hàng ngay?</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.tracking') }}" class="px-4 py-2 rounded-xl bg-brand-accent text-white text-xs font-bold hover:bg-brand-accent-hover transition-colors">Tra cứu đơn hàng</a>
                    <a href="{{ route('books.index') }}" class="px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-brand-text text-xs font-bold hover:border-brand-accent transition-colors">Khám phá tủ sách</a>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.faq-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('aria-controls');
                const panel = targetId ? document.getElementById(targetId) : null;
                if (!panel) return;

                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                panel.classList.toggle('hidden', isExpanded);

                const indicator = button.querySelector('.faq-indicator');
                if (indicator) {
                    indicator.textContent = isExpanded ? '[+]' : '[-]';
                }
            });
        });
    });
</script>
@endsection

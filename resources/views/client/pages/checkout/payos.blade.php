@extends('client.layouts.master')

@section('title', 'Chuyển khoản thanh toán #' . $order->order_number . ' - MNM Bookstore')

@section('content')
<div class="py-10 max-w-4xl mx-auto font-ui">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text">
                Thanh toán chuyển khoản ngân hàng
            </h1>
            <p class="text-sm text-brand-muted mt-1">
                Đơn hàng <span class="font-mono font-bold text-brand-accent">{{ $order->order_number }}</span>
            </p>
        </div>

        <span id="payment-status-badge" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border bg-amber-100 text-amber-800 border-amber-200">
            Đang chờ chuyển khoản...
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        <!-- Cot trai: Ma QR VietQR tu sinh -->
        <div class="md:col-span-5 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm text-center">
            <h2 class="font-display text-lg font-bold text-brand-text mb-2">Quét mã VietQR</h2>
            <p class="text-xs text-brand-muted mb-5">
                Mở ứng dụng ngân hàng bất kỳ và quét mã dưới đây. Số tiền và nội dung đã được điền tự động.
            </p>

            <div class="inline-flex items-center justify-center p-4 bg-white border border-brand-border rounded-2xl shadow-xs mb-4">
                <img id="vietqr-image" src="" alt="Mã VietQR thanh toán đơn hàng {{ $order->order_number }}" class="w-60 h-60 object-contain">
            </div>

            <div class="text-xs text-brand-muted leading-relaxed">
                Hệ thống tự động kiểm tra giao dịch mỗi 3 giây và chuyển trang ngay khi nhận được tiền.
            </div>
        </div>

        <!-- Cot phai: Thong tin chuyen khoan thu cong -->
        <div class="md:col-span-7 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-lg font-bold text-brand-text mb-4 pb-3 border-b border-brand-border">
                Thông tin chuyển khoản thủ công
            </h2>

            <div class="space-y-4 text-sm">
                <div class="p-3.5 bg-brand-surface rounded-xl border border-brand-border/70">
                    <div class="text-xs text-brand-muted mb-1">Ngân hàng thụ hưởng</div>
                    <div class="font-bold text-brand-text">{{ $bankName }}</div>
                </div>

                <div class="p-3.5 bg-brand-surface rounded-xl border border-brand-border/70 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-brand-muted mb-1">Số tài khoản</div>
                        <div class="font-mono text-base font-bold text-brand-text">{{ $accountNumber }}</div>
                    </div>
                    <button type="button" onclick="copyText('{{ $accountNumber }}', this)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors cursor-pointer">
                        Sao chép
                    </button>
                </div>

                <div class="p-3.5 bg-brand-surface rounded-xl border border-brand-border/70">
                    <div class="text-xs text-brand-muted mb-1">Chủ tài khoản</div>
                    <div class="font-bold text-brand-text">{{ $accountName }}</div>
                </div>

                <div class="p-3.5 bg-brand-surface rounded-xl border border-brand-border/70 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-brand-muted mb-1">Số tiền cần chuyển</div>
                        <div class="font-display text-xl font-bold text-brand-accent">{{ number_format($amount, 0, ',', '.') }} ₫</div>
                    </div>
                    <button type="button" onclick="copyText('{{ $amount }}', this)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-brand-border text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors cursor-pointer">
                        Sao chép
                    </button>
                </div>

                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-amber-800 font-semibold mb-1">Nội dung chuyển khoản (bắt buộc nhập chính xác)</div>
                        <div class="font-mono text-base font-bold text-brand-text">{{ $description }}</div>
                    </div>
                    <button type="button" onclick="copyText('{{ $description }}', this)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition-colors cursor-pointer">
                        Sao chép
                    </button>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-brand-border flex flex-col sm:flex-row items-center gap-3">
                <button type="button" id="btn-check-status" onclick="checkPaymentStatusNow(true)" class="w-full sm:flex-1 font-bold text-sm text-white bg-brand-accent hover:bg-brand-accent-hover px-5 py-3 rounded-xl shadow-soft hover:shadow-hover transition-all cursor-pointer">
                    Tôi đã chuyển khoản
                </button>

                <form method="POST" action="{{ route('checkout.payos.cancel-order', $order->order_number) }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" onclick="return confirm('Bạn muốn hủy giao dịch chuyển khoản này?')" class="w-full font-bold text-sm text-brand-text bg-brand-surface hover:bg-brand-border px-5 py-3 rounded-xl transition-colors cursor-pointer">
                        Hủy thanh toán
                    </button>
                </form>
            </div>

            <p id="check-status-feedback" class="text-xs text-brand-muted mt-3 hidden"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.renderVietQR === 'function') {
            window.renderVietQR('vietqr-image', @json($qrCode));
        }
    });

    function copyText(value, buttonEl) {
        if (!navigator.clipboard) {
            return;
        }
        navigator.clipboard.writeText(value).then(function () {
            const originalText = buttonEl.textContent;
            buttonEl.textContent = 'Đã chép';
            setTimeout(function () {
                buttonEl.textContent = originalText;
            }, 1500);
        });
    }

    const statusUrl = @json(route('checkout.payos.status', $order->order_number));
    let isChecking = false;

    async function checkPaymentStatusNow(isManualClick = false) {
        if (isChecking) {
            return;
        }
        isChecking = true;

        const feedbackEl = document.getElementById('check-status-feedback');
        const btnEl = document.getElementById('btn-check-status');

        if (isManualClick && btnEl) {
            btnEl.textContent = 'Đang kiểm tra...';
            btnEl.disabled = true;
        }

        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' },
            });
            const result = await response.json();

            if (result.paid && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            if (result.cancelled && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            if (isManualClick && feedbackEl) {
                feedbackEl.textContent = 'Chưa ghi nhận giao dịch mới từ ngân hàng. Nếu bạn vừa chuyển khoản, vui lòng đợi vài giây rồi thử lại.';
                feedbackEl.classList.remove('hidden');
            }
        } catch (error) {
            if (isManualClick && feedbackEl) {
                feedbackEl.textContent = 'Không thể kiểm tra trạng thái lúc này, vui lòng thử lại.';
                feedbackEl.classList.remove('hidden');
            }
        } finally {
            isChecking = false;
            if (isManualClick && btnEl) {
                btnEl.textContent = 'Tôi đã chuyển khoản';
                btnEl.disabled = false;
            }
        }
    }

    setInterval(function () {
        checkPaymentStatusNow(false);
    }, 3000);
</script>
@endsection

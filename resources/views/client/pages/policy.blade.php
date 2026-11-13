@extends('client.layouts.master')

@section('title', 'Chính sách & Điều khoản - MNM Bookstore')

@section('content')
<div class="py-10 max-w-4xl mx-auto font-ui">
    <nav aria-label="Breadcrumb" class="text-xs text-brand-muted mb-4 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-brand-accent transition-colors">Trang chủ</a>
        <span>/</span>
        <span class="text-brand-text font-medium">Chính sách & Điều khoản</span>
    </nav>

    <div class="mb-8 pb-6 border-b border-brand-border">
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-brand-accent mb-2">Cam kết minh bạch</span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-brand-text tracking-tight mb-3">
            Chính sách bảo mật & Điều khoản dịch vụ
        </h1>
        <p class="text-brand-muted text-sm md:text-base leading-relaxed">
            Quy định về việc bảo vệ thông tin cá nhân và quyền, nghĩa vụ khi đặt mua sách trên hệ thống MNM Bookstore.
        </p>
    </div>

    <div class="space-y-8">
        <section id="bao-mat" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Chính sách bảo mật thông tin</h2>
            <div class="space-y-3 text-sm text-brand-text/90 leading-relaxed">
                <p>MNM Bookstore chỉ thu thập các thông tin cần thiết phục vụ xác nhận đơn hàng, giao nhận và hỗ trợ khách hàng bao gồm họ tên, email, số điện thoại và địa chỉ giao hàng.</p>
                <p>Mật khẩu tài khoản được băm một chiều an toàn (Bcrypt) trước khi lưu trữ. Thông tin giao dịch chuyển khoản qua VietQR / payOS được xác thực bằng chữ ký HMAC-SHA256 và không lưu trữ thông tin thẻ ngân hàng nhạy cảm của người dùng.</p>
                <p>Chúng tôi không chia sẻ, bán hoặc trao đổi dữ liệu cá nhân của khách hàng cho bên thứ ba ngoại trừ đơn vị vận chuyển thực hiện giao hàng theo yêu cầu của chính đơn hàng đó.</p>
            </div>
        </section>

        <section id="dieu-khoan" class="scroll-mt-24 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h2 class="font-display text-2xl font-bold text-brand-text mb-4">Điều khoản dịch vụ</h2>
            <ul class="space-y-3 text-sm text-brand-text/90 leading-relaxed list-disc pl-5">
                <li><strong>Độ chính xác đơn hàng:</strong> Khách hàng vui lòng cung cấp chính xác số điện thoại và địa chỉ nhận hàng để tránh phát sinh chậm trễ hoặc hoàn đơn.</li>
                <li><strong>Tồn kho & giá bán:</strong> Số lượng sách hiển thị là tồn kho thực tế tại thời điểm đặt; hệ thống giữ kho ngay khi đơn được xác nhận tạo thành công.</li>
                <li><strong>Sử dụng mã giảm giá:</strong> Mỗi đơn hàng được áp dụng tối đa một mã giảm giá đáp ứng điều kiện giá trị tối thiểu, thời hạn và giới hạn lượt sử dụng.</li>
                <li><strong>Đánh giá sản phẩm:</strong> Nội dung bình luận cần trung thực, tôn trọng cộng đồng độc giả và không chứa thông tin spam hoặc liên kết độc hại.</li>
            </ul>

            <div class="mt-6 pt-5 border-t border-brand-border flex items-center justify-between flex-wrap gap-3">
                <a href="{{ route('pages.support') }}" class="text-sm font-semibold text-brand-accent underline hover:text-brand-accent-hover">Xem trang Vận chuyển, Đổi trả & FAQ</a>
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl bg-brand-text text-white text-xs font-bold hover:bg-brand-accent transition-colors">Quay lại mua sắm</a>
            </div>
        </section>
    </div>
</div>
@endsection

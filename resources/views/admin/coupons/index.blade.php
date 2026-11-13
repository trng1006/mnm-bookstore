@extends('admin.layouts.master')

@section('title', 'Quản lý Mã giảm giá')

@section('content')
<div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
    <div class="p-4 border-b border-brand-border flex justify-between items-center bg-gray-50">
        <h2 class="font-semibold text-brand-text">Danh sách mã giảm giá</h2>
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-brand-accent text-white rounded-lg hover:bg-brand-accent-hover text-sm font-medium transition-colors">
            + Thêm mã giảm giá
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-brand-muted text-xs uppercase tracking-wider border-b border-brand-border">
                    <th class="px-6 py-3 font-medium">Mã</th>
                    <th class="px-6 py-3 font-medium">Mức giảm</th>
                    <th class="px-6 py-3 font-medium">Đơn tối thiểu</th>
                    <th class="px-6 py-3 font-medium">Lượt dùng</th>
                    <th class="px-6 py-3 font-medium">Hạn sử dụng</th>
                    <th class="px-6 py-3 font-medium">Trạng thái</th>
                    <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm">
                @forelse($coupons as $coupon)
                @php
                    $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                    $isDepleted = !is_null($coupon->usage_limit) && $coupon->used_count >= $coupon->usage_limit;
                @endphp
                <tr class="hover:bg-brand-surface/50">
                    <td class="px-6 py-4 font-mono font-bold text-brand-text">{{ $coupon->code }}</td>
                    <td class="px-6 py-4 font-medium text-brand-accent">
                        {{ $coupon->type === 'percent' ? $coupon->value . '%' : number_format($coupon->value, 0, ',', '.') . ' ₫' }}
                    </td>
                    <td class="px-6 py-4 text-brand-text">
                        {{ number_format($coupon->min_order_value, 0, ',', '.') }} ₫
                    </td>
                    <td class="px-6 py-4 text-brand-muted">
                        {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? 'Không giới hạn' }}
                    </td>
                    <td class="px-6 py-4 text-brand-muted">
                        {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y H:i') : 'Vĩnh viễn' }}
                    </td>
                    <td class="px-6 py-4">
                        @if(!$coupon->is_active)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">Tạm khóa</span>
                        @elseif($isExpired)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Hết hạn</span>
                        @elseif($isDepleted)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">Hết lượt</span>
                        @else
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Đang hoạt động</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 hover:underline font-medium">Sửa</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa mã giảm giá này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline font-medium cursor-pointer">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-brand-muted">Chưa có mã giảm giá nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-brand-border">
        {{ $coupons->links() }}
    </div>
</div>
@endsection

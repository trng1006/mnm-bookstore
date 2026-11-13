@if($errors->any())
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="coupon-code" class="block text-sm font-medium text-brand-text mb-1">Mã giảm giá</label>
        <input type="text" id="coupon-code" name="code" value="{{ old('code', $coupon->code ?? '') }}" placeholder="VD: GIAM15" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm uppercase font-mono focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent" required>
    </div>

    <div>
        <label for="coupon-type" class="block text-sm font-medium text-brand-text mb-1">Loại giảm giá</label>
        <select id="coupon-type" name="type" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent" required>
            <option value="percent" @selected(old('type', $coupon->type ?? 'percent') === 'percent')>Theo phần trăm (%)</option>
            <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>Số tiền cố định (VND)</option>
        </select>
    </div>

    <div>
        <label for="coupon-value" class="block text-sm font-medium text-brand-text mb-1">Giá trị giảm</label>
        <input type="number" id="coupon-value" name="value" min="1" value="{{ old('value', $coupon->value ?? '') }}" placeholder="VD: 10 hoặc 30000" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent" required>
    </div>

    <div>
        <label for="coupon-min-order" class="block text-sm font-medium text-brand-text mb-1">Đơn tối thiểu (VND)</label>
        <input type="number" id="coupon-min-order" name="min_order_value" min="0" value="{{ old('min_order_value', $coupon->min_order_value ?? 0) }}" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent">
    </div>

    <div>
        <label for="coupon-usage-limit" class="block text-sm font-medium text-brand-text mb-1">Giới hạn lượt dùng (Để trống nếu không giới hạn)</label>
        <input type="number" id="coupon-usage-limit" name="usage_limit" min="1" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="Không giới hạn" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent">
    </div>

    <div>
        <label for="coupon-expires-at" class="block text-sm font-medium text-brand-text mb-1">Ngày hết hạn (Tùy chọn)</label>
        <input type="datetime-local" id="coupon-expires-at" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent/25 focus:border-brand-accent">
    </div>
</div>

<div class="pt-2">
    <label for="coupon-is-active" class="inline-flex items-center gap-2.5 cursor-pointer">
        <input type="checkbox" id="coupon-is-active" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true)) class="w-4 h-4 rounded border-brand-border text-brand-accent focus:ring-brand-accent">
        <span class="text-sm font-medium text-brand-text">Kích hoạt mã giảm giá ngay</span>
    </label>
</div>

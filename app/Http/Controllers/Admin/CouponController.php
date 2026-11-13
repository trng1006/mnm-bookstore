<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    /**
     * Hiển thị danh sách mã giảm giá.
     */
    public function index(): View
    {
        $coupons = Coupon::orderByDesc('id')->paginate(10);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Hiển thị form tạo mã giảm giá mới.
     */
    public function create(): View
    {
        return view('admin.coupons.create');
    }

    /**
     * Lưu mã giảm giá mới vào cơ sở dữ liệu.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCoupon($request);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Mã giảm giá đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa mã giảm giá.
     */
    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Cập nhật thông tin mã giảm giá.
     */
    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $this->validateCoupon($request, $coupon);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Mã giảm giá đã được cập nhật.');
    }

    /**
     * Xóa mã giảm giá khỏi hệ thống.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Mã giảm giá đã được xóa.');
    }

    /**
     * Chuẩn hóa và kiểm tra dữ liệu đầu vào cho Coupon.
     */
    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $maxValue = $request->input('type') === 'percent' ? 100 : 100000000;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'integer', 'min:1', 'max:' . $maxValue],
            'min_order_value' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ], [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.alpha_dash' => 'Mã giảm giá chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.',
            'code.unique' => 'Mã giảm giá này đã tồn tại.',
            'value.max' => $request->input('type') === 'percent'
                ? 'Giá trị giảm theo phần trăm không được vượt quá 100%.'
                : 'Giá trị giảm cố định vượt quá giới hạn cho phép.',
        ]);

        $validated['min_order_value'] = (int) ($validated['min_order_value'] ?? 0);
        $validated['usage_limit'] = isset($validated['usage_limit']) && $validated['usage_limit'] !== ''
            ? (int) $validated['usage_limit']
            : null;
        $validated['expires_at'] = !empty($validated['expires_at']) ? $validated['expires_at'] : null;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

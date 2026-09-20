<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property string $shipping_address
 * @property string|null $shipping_notes
 * @property string $payment_method
 * @property string $payment_status
 * @property string $status
 * @property int $subtotal
 * @property int $shipping_fee
 * @property int $discount_amount
 * @property int $total_amount
 * @property string|null $coupon_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, OrderStatusHistory> $statusHistories
 * @property-read string $status_label
 * @property-read string $status_badge_class
 * @property-read string $payment_method_label
 * @property-read string $payment_status_label
 * @property-read string $payment_status_badge_class
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order with($relations)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order create(array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order latest($column = null)
 * @method static int count($columns = '*')
 */
class Order extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PACKING = 'packing';
    public const STATUS_SHIPPING = 'shipping';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PAYMENT_FAILED = 'payment_failed';

    public static array $allowedTransitions = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_PAYMENT_FAILED],
        self::STATUS_CONFIRMED => [self::STATUS_PACKING],
        self::STATUS_PACKING => [self::STATUS_SHIPPING],
        self::STATUS_SHIPPING => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
        self::STATUS_PAYMENT_FAILED => [self::STATUS_PENDING, self::STATUS_CANCELLED],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_PACKING => 'Đang đóng gói',
            self::STATUS_SHIPPING => 'Đang giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_PAYMENT_FAILED => 'Thanh toán thất bại',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800 border-amber-200',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_PACKING => 'bg-purple-100 text-purple-800 border-purple-200',
            self::STATUS_SHIPPING => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800 border-red-200',
            self::STATUS_PAYMENT_FAILED => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            default => $this->payment_method,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Đã thanh toán',
            'pending' => 'Chưa thanh toán',
            default => $this->payment_status,
        };
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public static function generateOrderNumber(): string
    {
        return 'MNM-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
    }
}

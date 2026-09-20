<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentTransaction extends Model {
    protected $fillable = ['order_id', 'transaction_id', 'provider', 'amount', 'status', 'gateway_response'];
    protected $casts = [
        'gateway_response' => 'array',
    ];
    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }
}

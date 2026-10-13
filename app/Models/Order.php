<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'fulfillment_type',
        'subtotal', 'discount_amount', 'shipping_amount', 'total',
        'coupon_id', 'gift_voucher_id', 'voucher_amount', 'shipping_address_id', 'contact_email', 'contact_phone',
        'payment_method', 'payment_status', 'notes', 'placed_at',
    ];

    protected function casts(): array
    {
        return ['placed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function giftVoucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function totalRupees(): float
    {
        return $this->total / 100;
    }

    /** What is left to pay after wallet / gift card credit (voucher_amount). */
    public function amountDue(): int
    {
        return max(0, $this->total - $this->voucher_amount);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid', 'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled', 'refunded' => 'danger',
            default => 'secondary',
        };
    }

    public static function generateOrderNumber(): string
    {
        return 'RCB-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}

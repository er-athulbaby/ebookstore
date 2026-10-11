<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_uses', 'used_count', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** @param int $subtotal Subtotal in paise. */
    public function isValidFor(int $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return $subtotal >= $this->min_order_amount;
    }

    /** @param int $subtotal Subtotal in paise. Returns discount in paise. */
    public function discountFor(int $subtotal): int
    {
        if ($this->type === 'percent') {
            return (int) round($subtotal * ($this->value / 100));
        }

        return min($this->value, $subtotal);
    }
}

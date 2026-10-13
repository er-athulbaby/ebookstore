<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'book_variant_id', 'bundle_id', 'gift_card_value', 'gift_card_design_id', 'gift_card_meta', 'gift_voucher_id', 'book_title', 'variant_type', 'unit_price', 'quantity', 'line_total',
    ];

    protected $casts = ['gift_card_meta' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(BookVariant::class, 'book_variant_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function giftVoucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class);
    }

    public function ebookLicense(): HasOne
    {
        return $this->hasOne(EbookLicense::class);
    }

    public function isBundle(): bool
    {
        return $this->variant_type === 'bundle';
    }

    public function isGiftCard(): bool
    {
        return $this->variant_type === 'gift_card';
    }

    public function isEbook(): bool
    {
        return $this->variant_type === 'ebook';
    }
}

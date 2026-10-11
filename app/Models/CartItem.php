<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'book_variant_id', 'bundle_id', 'gift_card_value', 'quantity'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(BookVariant::class, 'book_variant_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function isBundle(): bool
    {
        return ! is_null($this->bundle_id);
    }

    public function isGiftCard(): bool
    {
        return ! is_null($this->gift_card_value);
    }

    public function isPhysical(): bool
    {
        if ($this->isGiftCard()) {
            return false;
        }

        return $this->isBundle() || $this->variant->isPhysical();
    }

    public function isEbook(): bool
    {
        return ! $this->isBundle() && ! $this->isGiftCard() && $this->variant->isEbook();
    }

    public function unitPrice(): int
    {
        if ($this->isGiftCard()) {
            return $this->gift_card_value;
        }

        return $this->isBundle() ? $this->bundle->price : $this->variant->price;
    }

    public function lineTotal(): int
    {
        return $this->unitPrice() * $this->quantity;
    }

    public function displayTitle(): string
    {
        if ($this->isGiftCard()) {
            return 'Gift Card — '.money($this->gift_card_value);
        }

        return $this->isBundle() ? $this->bundle->name : $this->variant->book->title;
    }

    public function displaySubtitle(): string
    {
        if ($this->isGiftCard()) {
            return 'Emailed to you after payment';
        }

        return $this->isBundle()
            ? $this->bundle->variants->count().'-book bundle'
            : $this->variant->typeLabel();
    }

    public function coverUrl(): string
    {
        if ($this->isGiftCard()) {
            return asset('assets/img/gift-card-placeholder.svg');
        }

        return $this->isBundle() ? $this->bundle->coverUrl() : $this->variant->book->coverUrl();
    }

    public function isAvailable(): bool
    {
        if ($this->isGiftCard()) {
            return true;
        }

        return $this->isBundle() ? $this->bundle->isAvailable() : ($this->variant->is_active && $this->variant->inStock());
    }
}

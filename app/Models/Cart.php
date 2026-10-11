<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->lineTotal());
    }

    public function totalQuantity(): int
    {
        return $this->items->sum('quantity');
    }

    public function hasPhysicalItems(): bool
    {
        return $this->items->contains(fn (CartItem $item) => $item->isPhysical());
    }

    public function hasDigitalItems(): bool
    {
        return $this->items->contains(fn (CartItem $item) => $item->isEbook());
    }

    public function hasGiftCardItems(): bool
    {
        return $this->items->contains(fn (CartItem $item) => $item->isGiftCard());
    }
}

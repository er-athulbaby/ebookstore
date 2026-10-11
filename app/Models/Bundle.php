<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image_path', 'price', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(BookVariant::class, 'bundle_book_variant');
    }

    public function priceRupees(): float
    {
        return $this->price / 100;
    }

    /** Sum of the individual variants' prices — what the bundle would cost bought separately. */
    public function compareAtPrice(): int
    {
        return $this->variants->sum('price');
    }

    public function savings(): int
    {
        return max(0, $this->compareAtPrice() - $this->price);
    }

    public function coverUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return $this->variants->first()?->book?->coverUrl() ?? asset('assets/img/book-placeholder.svg');
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->variants->isNotEmpty()
            && $this->variants->every(fn (BookVariant $v) => $v->is_active && $v->inStock());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id', 'type', 'sku', 'price', 'compare_at_price',
        'stock_quantity', 'weight_grams',
        'ebook_format', 'ebook_file_path', 'ebook_file_size',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isEbook(): bool
    {
        return $this->type === 'ebook';
    }

    public function isPhysical(): bool
    {
        return ! $this->isEbook();
    }

    public function inStock(): bool
    {
        if ($this->isEbook()) {
            return true;
        }

        return is_null($this->stock_quantity) || $this->stock_quantity > 0;
    }

    public function discountPercent(): int
    {
        if (! $this->compare_at_price || $this->compare_at_price <= $this->price) {
            return 0;
        }

        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    public function priceRupees(): float
    {
        return $this->price / 100;
    }

    public function compareAtPriceRupees(): ?float
    {
        return $this->compare_at_price ? $this->compare_at_price / 100 : null;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'paperback' => 'Paperback',
            'hardcover' => 'Hardcover',
            'ebook' => 'eBook ('.strtoupper($this->ebook_format ?? 'PDF').')',
        };
    }
}

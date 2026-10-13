<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'category_id', 'title', 'slug', 'description', 'publisher', 'isbn',
        'edition', 'pages', 'language', 'published_at',
        'is_featured', 'is_bestseller', 'is_top100', 'view_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_top100' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(BookVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BookImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function authorNames(): string
    {
        return $this->authors->pluck('name')->join(', ');
    }

    public function coverUrl(): string
    {
        $cover = $this->images->first();

        return $cover ? asset('storage/'.$cover->path) : asset('assets/img/book-placeholder.svg');
    }

    /** Lowest-priced active variant — used for "from ₹x" display on listing cards. */
    public function primaryVariant(): ?BookVariant
    {
        return $this->activeVariants->sortBy('price')->first();
    }

    public function physicalVariants(): \Illuminate\Support\Collection
    {
        return $this->activeVariants->whereIn('type', ['paperback', 'hardcover']);
    }

    public function ebookVariant(): ?BookVariant
    {
        return $this->activeVariants->firstWhere('type', 'ebook');
    }

    /**
     * Prefers the eager-loaded aggregate (withAvg('approvedReviews', 'rating')
     * on the query, aliased approved_reviews_avg_rating) when the caller set
     * one up — e.g. catalog listings, so 24 book cards don't each trigger
     * their own reviews query. Falls back to the loaded/lazy-loaded relation
     * collection on single-book pages where an aggregate wasn't requested.
     */
    public function averageRating(): float
    {
        if (array_key_exists('approved_reviews_avg_rating', $this->attributes)) {
            return round((float) $this->attributes['approved_reviews_avg_rating'], 1);
        }

        return round($this->approvedReviews->avg('rating') ?? 0, 1);
    }

    public function reviewCount(): int
    {
        if (array_key_exists('approved_reviews_count', $this->attributes)) {
            return (int) $this->attributes['approved_reviews_count'];
        }

        return $this->approvedReviews->count();
    }

    public function ratingBreakdown(): array
    {
        $total = $this->reviewCount();
        $counts = $this->approvedReviews->countBy('rating');

        $breakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $counts->get($star, 0);
            $breakdown[$star] = [
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return $breakdown;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EbookLicense extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_variant_id', 'order_item_id', 'download_limit', 'downloads_used'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(BookVariant::class, 'book_variant_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EbookDownloadLog::class);
    }

    public function canDownload(): bool
    {
        return $this->downloads_used < $this->download_limit;
    }

    public function remaining(): int
    {
        return max(0, $this->download_limit - $this->downloads_used);
    }
}

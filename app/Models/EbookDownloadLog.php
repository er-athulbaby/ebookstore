<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookDownloadLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['ebook_license_id', 'ip_address', 'user_agent', 'downloaded_at'];

    protected function casts(): array
    {
        return ['downloaded_at' => 'datetime'];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(EbookLicense::class, 'ebook_license_id');
    }
}

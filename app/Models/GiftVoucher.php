<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GiftVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'value', 'recipient_name', 'recipient_email', 'message', 'is_redeemed', 'redeemed_by_user_id', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_redeemed' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by_user_id');
    }

    public function isValid(): bool
    {
        if ($this->is_redeemed) {
            return false;
        }

        return ! $this->expires_at || ! $this->expires_at->isPast();
    }

    public static function generateCode(): string
    {
        do {
            $code = 'GIFT-'.strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}

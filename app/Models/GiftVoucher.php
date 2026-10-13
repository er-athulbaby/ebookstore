<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class GiftVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'value', 'recipient_name', 'recipient_email', 'sender_name', 'message', 'is_redeemed', 'redeemed_by_user_id', 'expires_at',
        'gift_card_design_id', 'purchased_by_user_id',
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

    public function design(): BelongsTo
    {
        return $this->belongsTo(GiftCardDesign::class, 'gift_card_design_id');
    }

    public function purchasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by_user_id');
    }

    /**
     * Adds the voucher's value to the user's wallet. Row-locked so the same code
     * entered twice at once (two tabs, double click) can only be credited once.
     * Returns the redeemed voucher, or null if the code is unknown, used or expired.
     */
    public static function redeemFor(string $code, User $user): ?self
    {
        $code = strtoupper(preg_replace('/\s+/', '', $code));

        return DB::transaction(function () use ($code, $user) {
            $voucher = static::where('code', $code)->lockForUpdate()->first();
            if (! $voucher || ! $voucher->isValid()) {
                return null;
            }
            $user->increment('wallet_balance', $voucher->value);
            $voucher->update(['is_redeemed' => true, 'redeemed_by_user_id' => $user->id]);

            return $voucher;
        });
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
        // No 0/O, 1/I/L: codes get read out and typed by hand.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $pick = fn () => collect(range(1, 4))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
            $code = 'GIFT-'.$pick().'-'.$pick();
        } while (static::where('code', $code)->exists());

        return $code;
    }
}

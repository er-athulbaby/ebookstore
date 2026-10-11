<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redemption is now decoupled from any single order: entering a code credits
 * the redeemer's wallet balance directly, so we track WHO redeemed it here
 * instead of which order it discounted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_vouchers', function (Blueprint $table) {
            $table->foreignId('redeemed_by_user_id')->nullable()->after('is_redeemed')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gift_vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('redeemed_by_user_id');
        });
    }
};

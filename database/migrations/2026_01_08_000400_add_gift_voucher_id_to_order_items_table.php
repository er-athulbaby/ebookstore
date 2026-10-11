<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a gift-card purchase line item to the voucher code it generated, so
 * the order confirmation/detail pages can show the code directly — relying
 * on email delivery alone is fragile (spam filters, mistyped addresses).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('gift_voucher_id')->nullable()->after('gift_card_value')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gift_voucher_id');
        });
    }
};

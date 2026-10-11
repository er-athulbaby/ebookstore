<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_voucher_id')->nullable()->after('coupon_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('voucher_amount')->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gift_voucher_id');
            $table->dropColumn('voucher_amount');
        });
    }
};

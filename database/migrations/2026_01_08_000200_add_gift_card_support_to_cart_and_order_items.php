<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A gift-card purchase is a third kind of cart/order line item — it isn't
 * tied to any catalog entity (unlike a variant or bundle), just a chosen
 * denomination — so it stores its value directly rather than an FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedInteger('gift_card_value')->nullable()->after('bundle_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('gift_card_value')->nullable()->after('bundle_id');
        });
        DB::statement("ALTER TABLE order_items MODIFY variant_type ENUM('paperback','hardcover','ebook','bundle','gift_card') NOT NULL");

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('razorpay','cod','wallet') NOT NULL DEFAULT 'razorpay'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('razorpay','cod') NOT NULL DEFAULT 'razorpay'");
        DB::statement("ALTER TABLE order_items MODIFY variant_type ENUM('paperback','hardcover','ebook','bundle') NOT NULL");

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('gift_card_value');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('gift_card_value');
        });
    }
};

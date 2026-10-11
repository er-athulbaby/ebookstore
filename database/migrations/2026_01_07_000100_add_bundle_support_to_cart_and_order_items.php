<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A cart/order line item is now either a book variant purchase OR a bundle
 * purchase, never both — hence both FK columns become nullable rather than
 * introducing a separate bundle_items table and duplicating cart/checkout logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cart_items MODIFY book_variant_id BIGINT UNSIGNED NULL');
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->after('book_variant_id')->constrained()->cascadeOnDelete();
            $table->unique(['cart_id', 'bundle_id']);
        });

        DB::statement('ALTER TABLE order_items MODIFY book_variant_id BIGINT UNSIGNED NULL');
        DB::statement("ALTER TABLE order_items MODIFY variant_type ENUM('paperback','hardcover','ebook','bundle') NOT NULL");
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->after('book_variant_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bundle_id');
        });
        DB::statement("ALTER TABLE order_items MODIFY variant_type ENUM('paperback','hardcover','ebook') NOT NULL");
        DB::statement('ALTER TABLE order_items MODIFY book_variant_id BIGINT UNSIGNED NOT NULL');

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'bundle_id']);
            $table->dropConstrainedForeignId('bundle_id');
        });
        DB::statement('ALTER TABLE cart_items MODIFY book_variant_id BIGINT UNSIGNED NOT NULL');
    }
};

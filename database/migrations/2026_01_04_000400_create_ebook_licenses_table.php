<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Granted the moment an order containing an ebook variant is marked
        // paid. This — not the order/payment status — is the single source
        // of truth the download controller checks against.
        Schema::create('ebook_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('download_limit')->default(5);
            $table->unsignedInteger('downloads_used')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'book_variant_id']);
        });

        Schema::create('ebook_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebook_license_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_download_logs');
        Schema::dropIfExists('ebook_licenses');
    }
};

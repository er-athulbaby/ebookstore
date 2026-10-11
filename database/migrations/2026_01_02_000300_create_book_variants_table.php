<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            // paperback / hardcover ship physically; ebook is a digital
            // download. A single title can carry any combination of the three.
            $table->enum('type', ['paperback', 'hardcover', 'ebook'])->index();
            $table->string('sku')->unique();

            $table->unsignedInteger('price'); // in paise (INR minor unit)
            $table->unsignedInteger('compare_at_price')->nullable(); // MRP, for the strike-through/%off badge

            // Physical only. Null stock on an ebook variant means "always available".
            $table->integer('stock_quantity')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();

            // Ebook only. File lives on the private `ebooks` disk, never web-served directly.
            $table->enum('ebook_format', ['pdf', 'epub'])->nullable();
            $table->string('ebook_file_path')->nullable();
            $table->unsignedBigInteger('ebook_file_size')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_variants');
    }
};

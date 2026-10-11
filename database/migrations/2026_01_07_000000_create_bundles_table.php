<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ponytail: bundles are physical-books-only (no ebook licensing/download
        // wiring for bundle items) — enforced in BundleResource's variant picker,
        // not the schema. Add ebook support here if bundles need to mix formats.
        Schema::create('bundle_book_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_variant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bundle_id', 'book_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_book_variant');
        Schema::dropIfExists('bundles');
    }
};

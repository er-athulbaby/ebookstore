<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('publisher')->default('RedCherry Books');
            $table->string('isbn')->nullable()->index();
            $table->string('edition')->nullable();
            $table->unsignedInteger('pages')->nullable();
            $table->string('language')->default('English');
            $table->date('published_at')->nullable();

            // Merchandising flags — drive the homepage rails (new releases, best
            // sellers, top 100) the same way the reference site's curated lists do.
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_top100')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamps();
        });

        // Many-to-many: most books have one author, but anthologies/combos
        // ("Group Of Authors" on the reference site) need more than one.
        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->primary(['author_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_book');
        Schema::dropIfExists('books');
    }
};

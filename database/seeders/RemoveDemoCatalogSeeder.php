<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Bundle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Removes the placeholder catalog that CatalogSeeder / BundleSeeder created.
 * Matches only these exact demo slugs, so real titles are never touched.
 * Books that appear in an order or ebook licence can't be deleted without
 * breaking order history, so those are unpublished and their variants disabled.
 */
class RemoveDemoCatalogSeeder extends Seeder
{
    private const DEMO_SLUGS = [
        'the-monsoon-diaries', 'silence-of-the-backwaters', 'letters-to-a-younger-self', 'the-founders-dilemma',
        'atomic-focus', 'the-quiet-discipline', 'empire-of-spice', 'the-last-ledger', 'grandmothers-kitchen',
        'a-river-called-home', 'markets-myths-and-money', 'the-compounding-mind', 'verses-for-a-rainy-evening',
        'the-cartographers-daughter', 'small-wins-big-change', 'the-twelve-hour-rule', 'curie-and-the-comet',
        'the-brave-little-boatman', 'empire-of-dust', 'the-long-return',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $books = Book::with('variants')->whereIn('slug', self::DEMO_SLUGS)->get();
            $variantIds = $books->flatMap->variants->pluck('id');

            // Bundles built from demo books stop making sense once those books are gone.
            $bundleIds = DB::table('bundle_book_variant')->whereIn('book_variant_id', $variantIds)->pluck('bundle_id')->unique();
            $bundles = Bundle::whereIn('id', $bundleIds)->update(['is_active' => false]);

            $deleted = $hidden = 0;
            foreach ($books as $book) {
                $ids = $book->variants->pluck('id');
                $ordered = DB::table('order_items')->whereIn('book_variant_id', $ids)->exists()
                    || DB::table('ebook_licenses')->whereIn('book_variant_id', $ids)->exists();

                if ($ordered) {
                    $book->update(['status' => 'draft', 'is_featured' => false, 'is_bestseller' => false, 'is_top100' => false]);
                    $book->variants()->update(['is_active' => false]);
                    $hidden++;
                } else {
                    $book->delete(); // cascades variants, images, reviews, wishlists, cart lines, bundle lines
                    $deleted++;
                }
            }

            $orphans = Author::doesntHave('books')->delete();

            $this->command?->info("Demo books deleted: {$deleted}, unpublished (have orders): {$hidden}, bundles disabled: {$bundles}, authors removed: {$orphans}");
        });
    }
}

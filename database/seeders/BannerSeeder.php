<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

/**
 * Starter hero slides (Unsplash photos, see storage/app/public/banners/CREDITS.md).
 * Swap for real marketing creative via /admin (Banners).
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Retire the original flat SVG placeholders.
        Banner::whereIn('image_path', ['banners/top100.svg', 'banners/new-releases.svg'])->update(['is_active' => false]);

        $slides = [
            ['banners/hero-browse.jpg', 'Find the book you didn’t know you needed', 'Our Top 100 — the titles readers keep coming back for.', 'Browse the Top 100', route('catalog.top100')],
            ['banners/hero-window.jpg', 'New on the shelf this week', 'Fresh paperbacks, hardcovers and eBooks, delivered across India.', 'See new releases', route('catalog.new-releases')],
            ['banners/hero-library.jpg', 'Better together: curated bundles', 'Hand-picked sets at one combo price.', 'Explore bundles', route('bundles.index')],
            ['banners/hero-gift.jpg', 'The gift that keeps being opened', 'RedCherry gift cards, delivered instantly by email.', 'Buy a gift card', route('gift-cards.index')],
        ];

        foreach ($slides as $i => [$image, $title, $subtitle, $cta, $link]) {
            Banner::updateOrCreate(
                ['image_path' => $image],
                ['title' => $title, 'subtitle' => $subtitle, 'cta_label' => $cta, 'link_url' => $link, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}

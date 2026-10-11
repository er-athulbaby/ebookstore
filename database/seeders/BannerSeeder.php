<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

/**
 * Placeholder hero banners so the homepage slider isn't empty out of the box.
 * Swap for real marketing creative via /admin (Banners).
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::updateOrCreate(
            ['image_path' => 'banners/top100.svg'],
            ['title' => 'RedCherry Top 100', 'link_url' => route('catalog.top100'), 'sort_order' => 1, 'is_active' => true]
        );

        Banner::updateOrCreate(
            ['image_path' => 'banners/new-releases.svg'],
            ['title' => 'New Releases', 'link_url' => route('catalog.new-releases'), 'sort_order' => 2, 'is_active' => true]
        );
    }
}

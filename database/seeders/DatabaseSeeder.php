<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            RedcherryTitlesSeeder::class, // real catalog; CatalogSeeder/BundleSeeder are the old demo data
            CouponSeeder::class,
            BannerSeeder::class,
            GiftVoucherSeeder::class,
        ]);
    }
}

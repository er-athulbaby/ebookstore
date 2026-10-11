<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\BookVariant;
use Illuminate\Database\Seeder;

class BundleSeeder extends Seeder
{
    public function run(): void
    {
        $variants = BookVariant::whereIn('sku', ['the-monsoon-diaries-pb', 'atomic-focus-pb'])->get();

        if ($variants->count() < 2) {
            return;
        }

        $bundle = Bundle::updateOrCreate(
            ['slug' => 'fiction-and-focus-starter-pack'],
            [
                'name' => 'Fiction & Focus Starter Pack',
                'description' => 'A bestselling novel plus a self-help favourite, bundled at one combo price.',
                'price' => (int) round($variants->sum('price') * 0.85),
                'is_active' => true,
            ]
        );

        $bundle->variants()->sync($variants->pluck('id'));
    }
}

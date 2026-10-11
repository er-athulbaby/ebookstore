<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(['code' => 'WELCOME10'], [
            'type' => 'percent',
            'value' => 10,
            'min_order_amount' => 30000, // ₹300
            'max_uses' => null,
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        Coupon::updateOrCreate(['code' => 'FLAT100'], [
            'type' => 'fixed',
            'value' => 10000, // ₹100
            'min_order_amount' => 60000, // ₹600
            'max_uses' => 500,
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);
    }
}

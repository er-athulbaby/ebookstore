<?php

namespace Database\Seeders;

use App\Models\GiftVoucher;
use Illuminate\Database\Seeder;

class GiftVoucherSeeder extends Seeder
{
    public function run(): void
    {
        GiftVoucher::updateOrCreate(
            ['code' => 'WELCOME100'],
            ['value' => 10000, 'recipient_name' => 'Sample Recipient', 'is_redeemed' => false]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@redcherrybooks.example'],
            [
                'name' => 'RedCherry Admin',
                'password' => Hash::make('password'), // CHANGE THIS after first login — see DEPLOYMENT.md
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

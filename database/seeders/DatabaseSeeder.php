<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────
        // Admin password is explicitly set to 123456789
        $admin = User::create([
            'first_name'     => 'ADMIN',
            'last_name'      => 'BHATTARAI',
            'phone'          => '9851280297',
            'email'          => 'neelamtpj@gmail.com',
            'password'       => Hash::make('admin@7'),
            'role'           => 'admin',
            'wallet_balance' => 0,
        ]);

        // ── Promo codes ───────────────────────────────────────────────
        PromoCode::create(['code' => 'BROCAR7', 'discount_type' => 'percentage', 'discount_value' => 40, 'max_uses' => 500, 'uses_count' => 23, 'min_fare' => 100, 'max_discount' => 200, 'expires_at' => now()->addMonths(3), 'is_active' => true]);

        $this->command->info('✅ BROCAR Database Seeded: 1 Admin only.');
    }
}
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');

        if (!$email || !$password) {
            if (app()->environment('production')) {
                throw new RuntimeException('INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD must be set before seeding production.');
            }

            $this->command?->warn('Skipping super admin seed because INITIAL_ADMIN_EMAIL or INITIAL_ADMIN_PASSWORD is not set.');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('INITIAL_ADMIN_NAME', 'admin'),
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'status' => 'active',
                'store_id' => 3, // super_admin 預設 store_id 為 3
                'can_manage_stores' => true,
                'can_manage_content' => true,
            ]
        );
    }
}

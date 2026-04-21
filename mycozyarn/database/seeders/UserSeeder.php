<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin mặc định — password tự hash qua cast 'password' => 'hashed' của User model.
        // Dùng firstOrCreate với match trên email để idempotent: chạy lại seeder
        // nhiều lần cũng không tạo trùng.
        User::firstOrCreate(
            ['email' => 'admin@cozyyarn.vn'],
            [
                'name'     => 'Admin CozyYarn',
                'password' => 'admin123',
                'phone'    => '0123456789',
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@cozyyarn.vn'],
            [
                'name'     => 'Khách hàng thử',
                'password' => 'user12345',
                'phone'    => '0987654321',
                'role'     => 'user',
                'status'   => 'active',
            ]
        );

        $this->command?->info('Seeded default users:');
        $this->command?->line('  • admin@cozyyarn.vn / admin123  (admin)');
        $this->command?->line('  • user@cozyyarn.vn  / user12345 (user)');
    }
}

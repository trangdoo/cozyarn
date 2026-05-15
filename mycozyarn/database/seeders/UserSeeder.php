<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User model mutator tự normalize SHA-256 + bcrypt nên seed truyền plaintext OK.
        // Dùng firstOrCreate với match trên email → idempotent.
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

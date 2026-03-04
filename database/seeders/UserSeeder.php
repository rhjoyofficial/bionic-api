<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'phone' => '0123456789',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin'); // Spatie magic

        // 2. Create Regular User
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0987654321',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('Customer');

    }
}

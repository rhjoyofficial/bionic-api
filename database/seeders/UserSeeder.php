<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define the Staff Members by Role
        $staffData = [
            'Super Admin' => [
                'Rakibul Hasan Joy',
                'C M Moin'
            ],
            'Admin' => [
                'Musa Islam Shawon'
            ],
            'Order Manager' => [
                'Nadim Islam'
            ],
            'Inventory Clerk' => [
                'Ritu Akter'
            ],
            'Marketing' => [
                'Tanzil Islam'
            ],
            'Customer Support' => [
                'Nusrat Fatema'
            ],
        ];

        // 2. Loop through and create staff users
        foreach ($staffData as $roleName => $names) {
            foreach ($names as $index => $name) {
                $this->createStaff($name, $roleName, $index);
            }
        }

        // 3. Create a few generic Customers for testing
        $customers = ['Abir Ahmed', 'Sumaiya Khan', 'Tanvir Hossain'];
        foreach ($customers as $index => $name) {
            $this->createStaff($name, 'Customer', $index);
        }
    }

    /**
     * Helper to create user, sanitize email, and assign role.
     */
    private function createStaff(string $name, string $role, int $index): void
    {
        // Generate a clean email: "Rakibul Hasan Joy" -> "rakibul.hasan.joy@bordeguna.com"
        $emailPrefix = Str::slug($name, '.');
        $email = "{$emailPrefix}@bordeguna.com";

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => $name,
                // Using a dummy phone logic; starts with 017 and pads with index
                'phone'     => '017333' . str_pad($index . rand(100, 999), 5, '0', STR_PAD_LEFT),
                'password'  => Hash::make('password'), 
                'is_active' => true,
            ]
        );

        // Assign the role defined in your RoleSeeder
        $user->syncRoles([$role]);
    }
}

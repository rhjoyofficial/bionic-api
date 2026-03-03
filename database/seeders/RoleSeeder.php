<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Category
            'category.view',
            'category.create',
            'category.update',
            'category.delete',

            // Product
            'product.view',
            'product.create',
            'product.update',
            'product.delete',

            // Order
            'order.view',
            'order.update',

            // Coupon
            'coupon.manage',

            // Shipping
            'shipping.manage',

            // User
            'user.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $customer = Role::firstOrCreate(['name' => 'Customer']);

        // Admin gets all permissions
        $admin->syncPermissions($permissions);

        // Customer gets minimal
        $customer->syncPermissions([
            'product.view',
        ]);
    }
}

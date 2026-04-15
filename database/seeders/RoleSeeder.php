<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── All permissions grouped by domain ────────────────────────────────
        $permissions = [
            // Category
            'category.view',
            'category.create',
            'category.update',
            'category.delete',

            // Product (includes combos, variants, tier-prices)
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'landing-pages.view',
            'landing-pages.create',
            'landing-pages.update',
            'landing-pages.delete',

            // Order
            'order.create',
            'order.view',
            'order.update',
            'order.export',

            // Coupon (granular — replaces old coupon.manage)
            'coupon.view',
            'coupon.create',
            'coupon.update',
            'coupon.delete',

            // Shipping (granular — replaces old shipping.manage)
            'shipping.view',
            'shipping.create',
            'shipping.update',
            'shipping.delete',

            // Customer
            'customer.view',
            'customer.update',
            'customer.deactivate',

            // Notifications
            'notification.view',
            'notification.send',
            'notification.manage',  // retry/delete failed jobs

            // System / Settings
            'system.settings',
            'system.webhooks',
            'system.activity_log',

            // Analytics
            'analytics.view',

            // Access Control
            'role.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── Roles ────────────────────────────────────────────────────────────

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions($permissions);

        // Admin — full operational access
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions($permissions);

        // Order Manager — orders, shipping view, customer view, notifications
        $orderManager = Role::firstOrCreate(['name' => 'Order Manager']);
        $orderManager->syncPermissions([
            'order.view',
            'order.update',
            'order.export',
            'shipping.view',
            'customer.view',
            'notification.view',
            'notification.send',
            'analytics.view',
        ]);

        // Inventory Clerk — products and categories only
        $inventoryClerk = Role::firstOrCreate(['name' => 'Inventory Clerk']);
        $inventoryClerk->syncPermissions([
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'category.view',
        ]);

        // Marketing — coupons, analytics, notifications
        $marketing = Role::firstOrCreate(['name' => 'Marketing']);
        $marketing->syncPermissions([
            'coupon.view',
            'coupon.create',
            'coupon.update',
            'coupon.delete',
            'analytics.view',
            'notification.view',
            'notification.send',
        ]);

        // Customer Support — view orders, manage customers, send notifications
        $support = Role::firstOrCreate(['name' => 'Customer Support']);
        $support->syncPermissions([
            'order.view',
            'customer.view',
            'customer.update',
            'notification.view',
            'notification.send',
        ]);

        // Customer (storefront) — minimal
        $customer = Role::firstOrCreate(['name' => 'Customer']);
        $customer->syncPermissions([
            'product.view',
        ]);

        // ── Remove legacy permissions that were replaced ─────────────────────
        Permission::whereIn('name', ['coupon.manage', 'shipping.manage', 'user.manage'])
            ->each(fn($p) => $p->delete());
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

            // Landing Pages
            'landing-pages.view',
            'landing-pages.create',
            'landing-pages.update',
            'landing-pages.delete',

            // Hero Banners
            'hero.view',
            'hero.create',
            'hero.update',
            'hero.delete',

            // Order
            'order.create',
            'order.view',
            'order.update',
            'order.export',

            // Coupon
            'coupon.view',
            'coupon.create',
            'coupon.update',
            'coupon.delete',

            // Shipping
            'shipping.view',
            'shipping.create',
            'shipping.update',
            'shipping.delete',

            // Customer
            'customer.view',
            'customer.create',
            'customer.update',
            'customer.delete',
            'customer.deactivate',
            'customer.change_password',

            // Notifications
            'notification.view',
            'notification.send',
            'notification.manage',

            // System / Settings
            'system.settings',
            'system.webhooks',
            'system.activity_log',

            // Analytics
            'analytics.view',

            // Access Control
            'role.manage',
            'permission.manage',

            // Admin Staff
            'staff.create',
            'staff.update',
            'staff.delete',
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
            'order.create',
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
            'category.create',
            'category.update',
        ]);

        // Marketing — coupons, hero banners, landing pages, analytics, notifications
        $marketing = Role::firstOrCreate(['name' => 'Marketing']);
        $marketing->syncPermissions([
            'coupon.view',
            'coupon.create',
            'coupon.update',
            'coupon.delete',
            'hero.view',
            'hero.create',
            'hero.update',
            'hero.delete',
            'landing-pages.view',
            'landing-pages.create',
            'landing-pages.update',
            'analytics.view',
            'notification.view',
            'notification.send',
        ]);

        // Customer Support — view orders, manage customers, send notifications
        $support = Role::firstOrCreate(['name' => 'Customer Support']);
        $support->syncPermissions([
            'order.view',
            'customer.view',
            'customer.create',
            'customer.update',
            'customer.deactivate',
            'customer.change_password',
            'notification.view',
            'notification.send',
            'analytics.view',
        ]);

        // Customer (storefront) — minimal read access
        $customer = Role::firstOrCreate(['name' => 'Customer']);
        $customer->syncPermissions([
            'product.view',
        ]);

        // Order Manager — re-sync to pick up any new customer perms (none needed here)
        // Marketing — no changes needed

        // ── Remove legacy permissions that were replaced ─────────────────────
        Permission::whereIn('name', ['coupon.manage', 'shipping.manage', 'user.manage'])
            ->each(fn ($p) => $p->delete());
    }
}

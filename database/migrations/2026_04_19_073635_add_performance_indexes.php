<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes for the most common public query patterns.
     *
     * Each index is wrapped in a conditional so the migration is safe to
     * re-run (idempotent) and won't error if the index already exists.
     */
    public function up(): void
    {
        // ── Products ──────────────────────────────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            // HomeController: active()->trending()
            if (! $this->hasIndex('products', 'products_is_active_is_trending_index')) {
                $table->index(['is_active', 'is_trending'], 'products_is_active_is_trending_index');
            }

            // CatalogController: active()->where('category_id', ...)
            if (! $this->hasIndex('products', 'products_is_active_category_id_index')) {
                $table->index(['is_active', 'category_id'], 'products_is_active_category_id_index');
            }

            // General listing: active()->latest()
            if (! $this->hasIndex('products', 'products_is_active_created_at_index')) {
                $table->index(['is_active', 'created_at'], 'products_is_active_created_at_index');
            }

            // LandingPage sync: where('landing_slug', ...)
            if (! $this->hasIndex('products', 'products_landing_slug_index')) {
                $table->index('landing_slug', 'products_landing_slug_index');
            }
        });

        // ── Product Variants ──────────────────────────────────────────────────
        Schema::table('product_variants', function (Blueprint $table) {
            // Product::variants() scope: hasMany()->where('is_active', true)->orderBy('id')
            if (! $this->hasIndex('product_variants', 'pv_product_id_is_active_index')) {
                $table->index(['product_id', 'is_active'], 'pv_product_id_is_active_index');
            }
        });

        // ── Landing Pages ─────────────────────────────────────────────────────
        Schema::table('landing_pages', function (Blueprint $table) {
            // LandingPageController::show(): where('slug', $slug)->where('is_active', true)
            if (! $this->hasIndex('landing_pages', 'landing_pages_slug_is_active_index')) {
                $table->index(['slug', 'is_active'], 'landing_pages_slug_is_active_index');
            }
        });

        // ── Categories ────────────────────────────────────────────────────────
        Schema::table('categories', function (Blueprint $table) {
            // ordered() scope: where('is_active', true)->orderBy('sort_order')
            if (! $this->hasIndex('categories', 'categories_is_active_sort_order_index')) {
                $table->index(['is_active', 'sort_order'], 'categories_is_active_sort_order_index');
            }
        });

        // ── Hero Banners ──────────────────────────────────────────────────────
        Schema::table('hero_banners', function (Blueprint $table) {
            // HomeController: active()->ordered()
            if (! $this->hasIndex('hero_banners', 'hero_banners_is_active_sort_order_index')) {
                $table->index(['is_active', 'sort_order'], 'hero_banners_is_active_sort_order_index');
            }
        });

        // ── Combos ────────────────────────────────────────────────────────────
        Schema::table('combos', function (Blueprint $table) {
            // HomeController: where('is_active', true)->latest()
            if (! $this->hasIndex('combos', 'combos_is_active_created_at_index')) {
                $table->index(['is_active', 'created_at'], 'combos_is_active_created_at_index');
            }
        });

        // ── Cart Items ────────────────────────────────────────────────────────
        Schema::table('cart_items', function (Blueprint $table) {
            // CartService: items()->where('variant_id', ...)
            if (! $this->hasIndex('cart_items', 'cart_items_cart_id_variant_id_index')) {
                $table->index(['cart_id', 'variant_id'], 'cart_items_cart_id_variant_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_is_active_is_trending_index');
            $table->dropIndexIfExists('products_is_active_category_id_index');
            $table->dropIndexIfExists('products_is_active_created_at_index');
            $table->dropIndexIfExists('products_landing_slug_index');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndexIfExists('pv_product_id_is_active_index');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropIndexIfExists('landing_pages_slug_is_active_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndexIfExists('categories_is_active_sort_order_index');
        });

        Schema::table('hero_banners', function (Blueprint $table) {
            $table->dropIndexIfExists('hero_banners_is_active_sort_order_index');
        });

        Schema::table('combos', function (Blueprint $table) {
            $table->dropIndexIfExists('combos_is_active_created_at_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndexIfExists('cart_items_cart_id_variant_id_index');
        });
    }

    /**
     * Check if an index already exists on the given table.
     * Prevents duplicate index errors on re-runs.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
    }
};

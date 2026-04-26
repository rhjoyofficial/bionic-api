<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a composite index on product_variants(is_active, price) to support
     * price range filtering and price-based sorting in CatalogController.
     * The migration is idempotent — safe to re-run.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (! $this->hasIndex('product_variants', 'pv_is_active_price_index')) {
                $table->index(['is_active', 'price'], 'pv_is_active_price_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndexIfExists('pv_is_active_price_index');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
    }
};

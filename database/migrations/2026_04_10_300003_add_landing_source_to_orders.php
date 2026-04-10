<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track where each order originated: checkout, landing, admin.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 30)->default('checkout')->after('order_status');
            $table->foreignId('landing_page_id')->nullable()->after('source')
                  ->constrained()->nullOnDelete();
        });

        // Add is_landing_enabled to combos (products already has it)
        if (!Schema::hasColumn('combos', 'is_landing_enabled')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->boolean('is_landing_enabled')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landing_page_id');
            $table->dropColumn('source');
        });

        if (Schema::hasColumn('combos', 'is_landing_enabled')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->dropColumn('is_landing_enabled');
            });
        }
    }
};

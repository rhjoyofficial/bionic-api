<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            // Explicit display order for the checkout zone picker.
            // Lower number appears first (e.g. Dhaka City = 1, Dhaka Suburb = 2).
            $table->unsignedSmallInteger('sort_order')->default(99)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};

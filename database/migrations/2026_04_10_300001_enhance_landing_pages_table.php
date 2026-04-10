<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            // Type: product, combo, or sales (multi-item)
            $table->string('type', 20)->default('product')->after('slug');

            // Combo support — nullable FK
            $table->foreignId('combo_id')->nullable()->after('product_id')
                  ->constrained()->nullOnDelete();

            // Make product_id nullable (sales type has no single product)
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // Blade template file name (e.g. 'product-default', 'sales-summer-sale')
            $table->string('blade_template')->default('product-default')->after('hero_image');

            // Flexible JSON config for landing-specific rules
            // e.g. {"free_delivery_qty": 3, "free_delivery_amount": 1000}
            $table->json('config')->nullable()->after('pixel_event_name');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('combo_id');
            $table->dropColumn(['type', 'blade_template', 'config']);
            // Revert product_id to NOT NULL would need data cleanup — left as-is
        });
    }
};

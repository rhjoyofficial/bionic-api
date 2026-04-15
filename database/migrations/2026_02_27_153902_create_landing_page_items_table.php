<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Items for sales-type landing pages (multi-product).
     * Each row = one product variant or combo that can be selected on the page.
     */
    public function up(): void
    {
        Schema::create('landing_page_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('landing_page_id')
                  ->constrained()->cascadeOnDelete();

            // Either a variant or a combo — mutually exclusive
            $table->foreignId('product_variant_id')->nullable()
                  ->constrained()->nullOnDelete();
            $table->foreignId('combo_id')->nullable()
                  ->constrained()->nullOnDelete();

            $table->boolean('is_preselected')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_items');
    }
};

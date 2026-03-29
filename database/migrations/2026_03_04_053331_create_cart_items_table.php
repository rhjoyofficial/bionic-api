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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants');
            $table->integer('quantity');

            $table->decimal('unit_price_snapshot', 12, 2);
            $table->string('product_name_snapshot')->nullable();
            $table->string('variant_title_snapshot')->nullable();
            $table->foreignId('combo_id')->nullable()->constrained()->nullOnDelete();
            $table->string('combo_name_snapshot')->nullable();

            $table->timestamps();

            $table->index(['cart_id', 'variant_id']);
            $table->unique(['cart_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};

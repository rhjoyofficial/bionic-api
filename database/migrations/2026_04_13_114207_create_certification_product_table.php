<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certification_product', function (Blueprint $table) {
            $table->id();

            // Link to Product
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete(); // If a product is deleted, remove the link

            // Link to Certification
            $table->foreignId('certification_id')
                ->constrained()
                ->cascadeOnDelete(); // If a cert is deleted, remove the link

            $table->timestamps();

            // Ensure a product can't be assigned the exact same certification twice
            $table->unique(['product_id', 'certification_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_product');
    }
};

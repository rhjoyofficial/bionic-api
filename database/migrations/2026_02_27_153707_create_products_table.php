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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('base_price', 10, 2);
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->string('landing_slug')->nullable()->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('is_landing_enabled')->default(false);

            $table->timestamps();

            $table->index(['category_id', 'is_active', 'is_trending']);
            $table->index('base_price');
            $table->index('is_featured');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

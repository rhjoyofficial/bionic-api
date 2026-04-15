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
        Schema::create('landing_pages', function (Blueprint $table) {
            // 1. Identity & Type
            $table->id();
            $table->string('slug')->unique();
            $table->string('type', 20)->default('product'); // product, combo, etc.

            // 2. Relationships (Foreign Keys)
            // Both are nullable because a landing page might be for a single product OR a combo.
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('combo_id')->nullable()->constrained()->nullOnDelete();

            // 3. Content & UI
            $table->string('title');
            $table->string('hero_image')->nullable();
            $table->string('blade_template')->default('product-default');
            $table->longText('content')->nullable();

            // 4. SEO, Marketing & Customization
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('pixel_event_name')->nullable();
            $table->json('config')->nullable(); // Stores design tweaks or extra settings

            // 5. System Control
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};

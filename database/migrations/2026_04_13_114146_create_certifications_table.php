<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();

            // Core Details
            $table->string('name');
            $table->string('category')->index(); // Indexed for faster group lookups
            $table->string('organization')->nullable();

            // Validity Dates
            $table->date('given_date')->nullable();
            $table->date('expiry_date')->nullable()->index(); // Indexed to easily query expiring certs

            // Long-form text
            $table->text('additional_details')->nullable();

            // Media (Paths to storage)
            $table->string('logo_path')->nullable();
            $table->string('image_path')->nullable(); // The actual scanned certificate

            // Status & Sorting
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Composite index for frontend querying (getting active certs in order)
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};

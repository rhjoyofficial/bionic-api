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
        Schema::create('social_proofs', function (Blueprint $table) {
            $table->id();
            $table->morphs('proofable'); // Can belong to anything

            // Customer Profile
            $table->string('customer_name');
            $table->string('customer_designation')->nullable(); // e.g. "Verified Buyer"
            $table->string('avatar_path')->nullable();

            // Type logic: 'text', 'voice', 'video', 'screenshot'
            $table->string('type')->default('text')->index();

            // Data Storage
            $table->text('content')->nullable(); // For text reviews
            $table->string('media_path')->nullable(); // For voice notes (mp3), screenshots, or local video
            $table->string('video_url')->nullable(); // For YouTube/Vimeo video reviews

            $table->unsignedTinyInteger('rating')->default(5); // 1-5 scale
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_proofs');
    }
};

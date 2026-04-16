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
        Schema::create('media_videos', function (Blueprint $table) {
            $table->id();

            // Polymorphic columns: allows video to belong to Product, LandingPage, etc.
            $table->morphs('videoable');

            $table->string('title')->nullable();

            // provider: 'youtube', 'vimeo', or 'local'
            $table->string('provider')->default('youtube')->index();

            // Stores the YouTube ID (e.g., dQw4w9WgXcQ) or the storage path (e.g., videos/promo.mp4)
            $table->text('video_id_or_path');

            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Index for fast filtering of active videos in order
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_videos');
    }
};

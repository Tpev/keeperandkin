<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dog_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->onDelete('cascade');

            // 'image' or 'video'
            $table->string('media_type')->default('image');

            // For uploaded files (images or videos)
            $table->string('file_path')->nullable();

            // For hosted videos (YouTube/Vimeo URL, etc.)
            $table->string('video_url')->nullable();

            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dog_media');
    }
};

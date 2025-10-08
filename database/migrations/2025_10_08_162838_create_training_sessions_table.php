<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('pdf_path')->nullable(); // storage path
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['team_id', 'is_active']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_sessions');
    }
};

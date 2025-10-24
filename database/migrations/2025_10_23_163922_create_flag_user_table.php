<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flag_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_flag_id')->constrained('training_flags')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending|in_progress|completed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'training_flag_id'], 'fu_user_flag_uq');
            $table->index(['user_id', 'status'], 'fu_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flag_user');
    }
};

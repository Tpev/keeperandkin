<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dog_training_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_flag_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained('evaluations')->nullOnDelete();

            $table->enum('status', ['pending','in_progress','completed','skipped'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['dog_id','training_session_id']);
            $table->index(['dog_id','status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('dog_training_assignments');
    }
};

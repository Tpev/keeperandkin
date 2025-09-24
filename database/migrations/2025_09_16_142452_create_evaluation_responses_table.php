<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();

            // Link to your existing evaluations table
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();

            // Snapshot of which form/version was used
            $table->foreignId('form_id')->constrained('evaluation_forms')->cascadeOnDelete();

            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            // For single-choice or boolean
            $table->foreignId('answer_option_id')->nullable()->constrained('answer_options')->nullOnDelete();

            // For text answers
            $table->text('answer_text')->nullable();

            // For scale numeric or custom numeric value
            $table->decimal('answer_value', 8, 2)->nullable();

            // For multi-choice or structured answers
            $table->json('answer_json')->nullable();

            $table->timestamps();

            $table->index(['evaluation_id', 'question_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('evaluation_responses');
    }
};

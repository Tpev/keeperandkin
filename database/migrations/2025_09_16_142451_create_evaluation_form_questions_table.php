<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evaluation_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('evaluation_forms')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('evaluation_sections')->nullOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            $table->unsignedInteger('position')->default(1);
            $table->boolean('required')->default(true);

            // always | staff_only | public_summary
            $table->string('visibility', 32)->default('always');

            $table->json('meta')->nullable(); // per-form overrides
            $table->timestamps();

            $table->unique(['form_id', 'question_id']);
            $table->index(['form_id', 'section_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('evaluation_form_questions');
    }
};

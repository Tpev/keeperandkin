<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_follow_ups', function (Blueprint $table) {
            $table->id();
            // Child is the question that will be conditionally shown
            $table->unsignedBigInteger('child_form_question_id');
            // Parent is the question that controls visibility
            $table->unsignedBigInteger('parent_form_question_id');
            // Which answer option IDs on the parent trigger the child to appear
            $table->json('trigger_option_ids')->nullable(); // array<int>
            // UI + validation behavior
            $table->enum('display_mode', ['inline_after_parent'])->default('inline_after_parent');
            $table->enum('required_mode', ['visible_only', 'always'])->default('visible_only');
            $table->timestamps();

            $table->foreign('child_form_question_id')
                ->references('id')->on('evaluation_form_questions')
                ->cascadeOnDelete();

            $table->foreign('parent_form_question_id')
                ->references('id')->on('evaluation_form_questions')
                ->cascadeOnDelete();

            // A child can have at most one follow-up rule
            $table->unique('child_form_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_follow_ups');
    }
};

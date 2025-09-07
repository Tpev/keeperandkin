<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_option_params', function (Blueprint $table) {
            $table->id();
            $table->string('category_key');                // e.g. "Confidence"
            $table->string('question_key');                // e.g. "confidence_env"
            $table->string('option_key');                  // e.g. "confident"

            $table->unsignedInteger('weight')->default(1); // per-answer weight
            $table->string('training_category')->nullable();
            $table->json('flags')->nullable();             // ["red_flag","safety",...]

            $table->timestamps();

            $table->unique(['question_key','option_key']);
            $table->index(['category_key','question_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_option_params');
    }
};

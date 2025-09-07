<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_question_params', function (Blueprint $table) {
            $table->id();
            // category key from config (e.g. "Confidence")
            $table->string('category_key');
            // question key from config (e.g. "confidence_env")
            $table->string('question_key')->unique();
            // admin-tunable fields
            $table->unsignedInteger('weight')->default(1);            // 1..5 typical
            $table->string('training_category')->nullable();          // e.g. "handling","social","obedience"
            $table->json('flags')->nullable();                        // ["red_flag","safety"...]
            $table->timestamps();

            $table->index(['category_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_question_params');
    }
};

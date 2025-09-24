<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evaluation_response_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('evaluation_responses')->cascadeOnDelete();
            $table->foreignId('answer_option_id')->constrained('answer_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['response_id', 'answer_option_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('evaluation_response_options');
    }
};

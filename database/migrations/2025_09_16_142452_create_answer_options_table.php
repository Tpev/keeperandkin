<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('label');
            $table->string('value')->nullable();   // optional machine value
            $table->unsignedInteger('position')->default(1);

            // Example: {"comfort_confidence":25,"sociability":0,"trainability":0}
            $table->json('score_map')->nullable();

            // Example: ["Bite Risk Dog","Muzzle Conditioning"]
            $table->json('flags')->nullable();

            $table->timestamps();

            $table->index(['question_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('answer_options');
    }
};

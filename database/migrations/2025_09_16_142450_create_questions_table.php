<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // stable identifier
            $table->text('prompt');
            $table->text('help_text')->nullable();

            // single_choice | multi_choice | scale | boolean | text
            $table->string('type', 32);

            // comfort_confidence | sociability | trainability | general
            $table->string('category', 32)->default('general');

            $table->json('meta')->nullable(); // e.g., {"min":0,"max":5,"invert":false}
            $table->timestamps();

            $table->index(['category', 'type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('questions');
    }
};

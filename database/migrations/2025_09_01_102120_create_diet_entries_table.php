<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('diet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->dateTime('fed_at');                              // when the feeding happened
            $table->string('meal')->nullable();                      // breakfast / lunch / dinner / snack
            $table->string('food')->nullable();                      // optional override if different food was used
            $table->decimal('grams', 6, 1)->nullable();
            $table->unsignedSmallInteger('calories')->nullable();    // kcal given
            $table->unsignedTinyInteger('appetite')->nullable();     // 1–5 (1 poor, 5 great)
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['dog_id','fed_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('diet_entries');
    }
};

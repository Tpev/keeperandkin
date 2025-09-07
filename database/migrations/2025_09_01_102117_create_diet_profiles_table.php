<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('diet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->string('food_brand')->nullable();
            $table->string('food_name')->nullable();                 // e.g., "Adult Salmon"
            $table->string('food_type')->nullable();                 // kibble / wet / raw / home-cooked
            $table->unsignedSmallInteger('daily_calories')->nullable(); // kcal/day target
            $table->unsignedTinyInteger('meals_per_day')->nullable();   // e.g., 2
            $table->decimal('portion_grams_per_meal', 6, 1)->nullable();
            $table->json('allergies')->nullable();                   // ["chicken","beef"]
            $table->json('supplements')->nullable();                 // ["omega-3","probiotic"]
            $table->text('notes')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->unique('dog_id'); // one profile per dog
        });
    }

    public function down(): void {
        Schema::dropIfExists('diet_profiles');
    }
};

<?php

// database/migrations/2025_09_01_000000_create_vet_metrics_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vet_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_weight', 5, 2)->nullable(); // kg
            $table->unsignedTinyInteger('bcs')->nullable(); // 1–9
            $table->date('next_vaccine_date')->nullable();
            $table->timestamps();
            $table->unique('dog_id'); // one-to-one per dog
        });
    }
    public function down(): void { Schema::dropIfExists('vet_metrics'); }
};

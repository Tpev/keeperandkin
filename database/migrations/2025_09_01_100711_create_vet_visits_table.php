<?php

// database/migrations/2025_09_01_000001_create_vet_visits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vet_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('reason', 160);
            $table->text('outcome')->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // kg at visit
            $table->timestamps();
            $table->index(['dog_id', 'visit_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('vet_visits'); }
};

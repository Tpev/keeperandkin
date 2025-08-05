<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')           // shelter
                  ->constrained('teams')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('breed')->nullable();
            $table->unsignedTinyInteger('age')->nullable();   // years
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dogs');
    }
};

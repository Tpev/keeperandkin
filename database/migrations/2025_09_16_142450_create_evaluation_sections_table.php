<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evaluation_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('evaluation_forms')->cascadeOnDelete();
            $table->string('title');      // e.g., 'Comfort & Confidence'
            $table->string('slug');       // 'comfort-confidence'
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->index(['form_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('evaluation_sections');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_flag_training_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_flag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['training_flag_id','training_session_id'], 'tfts_flag_session_uq');

        });
    }
    public function down(): void {
        Schema::dropIfExists('training_flag_training_session');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('answer_option_training_flag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_flag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['answer_option_id','training_flag_id'], 'aotf_option_flag_uq');

        });
    }
    public function down(): void {
        Schema::dropIfExists('answer_option_training_flag');
    }
};

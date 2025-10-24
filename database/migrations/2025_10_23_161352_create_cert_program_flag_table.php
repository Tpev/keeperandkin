<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cert_program_flag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cert_program_id')->constrained('cert_programs')->cascadeOnDelete();
            $table->foreignId('training_flag_id')->constrained('training_flags')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['cert_program_id','training_flag_id'], 'cpf_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_program_flag');
    }
};

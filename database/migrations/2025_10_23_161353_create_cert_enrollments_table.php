<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cert_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cert_program_id')->constrained('cert_programs')->cascadeOnDelete();
            $table->string('status', 20)->default('enrolled'); // enrolled|in_progress|completed
            $table->timestamps();

            $table->index(['cert_program_id','status'], 'ce_prog_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_enrollments');
    }
};

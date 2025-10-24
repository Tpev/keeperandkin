<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cert_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // visibility
            $table->string('visibility_mode', 20)->default('public'); // public|role_gated
            $table->json('required_roles')->nullable();              // JSON array; prep only (no enforcement in Phase 2)

            // status & meta
            $table->boolean('is_active')->default(true);
            $table->string('difficulty', 20)->nullable(); // beginner|intermediate|advanced (optional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_programs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dog_transfers', function (Blueprint $t) {
            $t->id()->startingValue(1000);
            $t->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $t->foreignId('from_team_id')->constrained('teams')->cascadeOnDelete();
            $t->foreignId('to_team_id')->nullable()->constrained('teams')->nullOnDelete();

            $t->string('to_email')->index();
            $t->enum('status', ['pending','accepted','declined','canceled','expired'])->default('pending')->index();

            $t->string('token_hash', 128)->index();
            $t->timestamp('expires_at')->index();

            $t->boolean('include_private_notes')->default(false);
            $t->boolean('include_adopter_pii')->default(false);

            $t->foreignId('initiator_user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('accepted_user_id')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('declined_at')->nullable();
            $t->timestamp('canceled_at')->nullable();

            // Optional counters for pretty summaries
            $t->unsignedInteger('count_evaluations')->default(0);
            $t->unsignedInteger('count_files')->default(0);
            $t->unsignedInteger('count_notes')->default(0);

            $t->json('meta')->nullable(); // room for extras
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('dog_transfers');
    }
};
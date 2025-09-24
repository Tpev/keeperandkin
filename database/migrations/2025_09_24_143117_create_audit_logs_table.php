<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->string('event');                 // e.g., transfer.initiated, transfer.accepted
            $t->morphs('subject');               // subject_type, subject_id (e.g., DogTransfer)
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('actor_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $t->json('context')->nullable();     // arbitrary payload
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};

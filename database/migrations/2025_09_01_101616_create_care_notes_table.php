<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('care_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // author (optional)
            $table->string('author_name', 120)->nullable();                           // fallback to display
            $table->text('body');                                                    // note content (plain/markdown/HTML)
            $table->timestamp('pinned_at')->nullable();                              // only one pinned per dog (we’ll enforce in app)
            $table->timestamps();

            $table->index(['dog_id', 'created_at']);
            $table->index(['dog_id', 'pinned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_notes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('evaluation_form_id')
                ->nullable()
                ->after('id')
                ->constrained('evaluation_forms')
                ->nullOnDelete();

            // Keep your existing columns like category_scores, red_flags, etc.
        });
    }

    public function down(): void {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evaluation_form_id');
        });
    }
};

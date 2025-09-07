<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('evaluations', function (Blueprint $table) {
        $table->json('red_flags')->nullable()->after('answers');
    });
}

public function down(): void
{
    Schema::table('evaluations', function (Blueprint $table) {
        $table->dropColumn('red_flags');
    });
}

};

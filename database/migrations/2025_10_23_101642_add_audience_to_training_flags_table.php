<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_flags', function (Blueprint $table) {
            // Backward-compatible: default to 'dog' so old flags remain valid
            $table->string('audience', 20)->default('dog')->index()->after('is_active');
        });

        // If you have any NULLs somehow, normalize them (defensive)
        DB::table('training_flags')
            ->whereNull('audience')
            ->update(['audience' => 'dog']);
    }

    public function down(): void
    {
        Schema::table('training_flags', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};

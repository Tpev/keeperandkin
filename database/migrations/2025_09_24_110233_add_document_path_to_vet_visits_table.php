<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vet_visits', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('weight'); // public disk path
        });
    }

    public function down(): void
    {
        Schema::table('vet_visits', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });
    }
};

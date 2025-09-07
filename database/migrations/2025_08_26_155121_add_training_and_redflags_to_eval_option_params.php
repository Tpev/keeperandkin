<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_option_params', function (Blueprint $table) {
            $table->json('training_tags')->nullable()->after('weight');
            $table->json('red_flags')->nullable()->after('training_tags');
        });

        // Optional one-time data move: carry single training_category -> training_tags[0]
        DB::table('evaluation_option_params')
            ->whereNotNull('training_category')
            ->update([
                'training_tags' => DB::raw("JSON_ARRAY(training_category)")
            ]);
    }

    public function down(): void
    {
        Schema::table('evaluation_option_params', function (Blueprint $table) {
            $table->dropColumn(['training_tags', 'red_flags']);
        });
    }
};

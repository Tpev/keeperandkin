<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dogs', function (Blueprint $table) {
            // Simple text fields
            $table->string('location')->nullable()->after('name');
            $table->date('approx_dob')->nullable()->after('location');        // store as DATE; format "US" will be handled in UI
            $table->boolean('fixed')->nullable()->after('approx_dob');        // Yes/No (nullable to represent unknown)
            $table->string('color')->nullable()->after('fixed');
            $table->string('size')->nullable()->after('color');

            // IDs & health
            $table->string('microchip')->nullable()->after('size')->index();  // indexed for quick lookup
            $table->text('heartworm')->nullable()->after('microchip');        // (you wrote "Heatworm"; assuming Heartworm)
            $table->text('fiv_l')->nullable()->after('heartworm');            // FIV/L (kept underscore name)
            $table->text('flv')->nullable()->after('fiv_l');                  // FLV

            // Temperament / home compatibility (kept as text to allow nuance)
            $table->text('housetrained')->nullable()->after('flv');
            $table->text('good_with_dogs')->nullable()->after('housetrained');
            $table->text('good_with_cats')->nullable()->after('good_with_dogs');
            $table->text('good_with_children')->nullable()->after('good_with_cats');
        });
    }

    public function down(): void
    {
        Schema::table('dogs', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'approx_dob',
                'fixed',
                'color',
                'size',
                'microchip',
                'heartworm',
                'fiv_l',
                'flv',
                'housetrained',
                'good_with_dogs',
                'good_with_cats',
                'good_with_children',
            ]);
        });
    }
};

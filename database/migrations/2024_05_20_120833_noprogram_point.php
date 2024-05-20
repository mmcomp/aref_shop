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
        Schema::table('reading_station_weekly_programs', function (Blueprint $table) {
            $table->integer('noprogram_point')->default(0)->afetr('package_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_weekly_programs', function (Blueprint $table) {
            $table->dropColumn('noprogram_point');
        });
    }
};

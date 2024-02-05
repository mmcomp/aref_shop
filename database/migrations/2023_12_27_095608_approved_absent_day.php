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
            $table->integer('approved_absent_day')->default(0)->after('absent_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_weekly_programs', function (Blueprint $table) {
            $table->dropColumn('approved_absent_day');
        });
    }
};

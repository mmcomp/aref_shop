<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reading_station_calls', function (Blueprint $table) {
            DB::statement("ALTER TABLE `reading_station_calls` CHANGE `reason` `reason` enum('exit', 'none_exit', 'all') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_calls', function (Blueprint $table) {
            DB::statement("ALTER TABLE `reading_station_calls` CHANGE `reason` `reason` enum('entry','exit','latency','absence','none_exit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL ");
        });
    }
};

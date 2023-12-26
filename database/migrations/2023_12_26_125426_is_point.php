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
        Schema::table('reading_station_strikes', function (Blueprint $table) {
            $table->boolean('is_point')->default(false)->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_strikes', function (Blueprint $table) {
            $table->dropColumn('is_point');
        });
    }
};

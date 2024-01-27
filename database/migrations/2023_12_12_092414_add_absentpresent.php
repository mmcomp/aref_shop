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
        Schema::table('reading_station_calls', function (Blueprint $table) {
            $table->integer('reading_station_absent_present_id')->nullable()->after('caller_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_calls', function (Blueprint $table) {
            $table->dropColumn('reading_station_absent_present_id');
        });
    }
};

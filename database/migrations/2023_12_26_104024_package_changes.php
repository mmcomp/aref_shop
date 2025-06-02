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
        Schema::table('reading_station_packages', function (Blueprint $table) {
            $table->integer('grade')->nullable()->after('optional_time');
            $table->integer('step')->nullable()->after('optional_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_packages', function (Blueprint $table) {
            $table->dropColumn('grade');
            $table->dropColumn('step');
        });
    }
};

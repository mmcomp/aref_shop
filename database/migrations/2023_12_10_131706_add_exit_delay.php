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
        Schema::table('reading_station_absent_presents', function (Blueprint $table) {
            $table->enum('exit_delay', ['none', '15-30', '30-45', '45-60', '60-75'])->nullable()->after('is_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_absent_presents', function (Blueprint $table) {
            $table->dropColumn('exit_delay');
        });
    }
};

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
        Schema::table('reading_station_slut_users', function (Blueprint $table) {
            $table->string("absense_file")->nullable()->after('absense_approved_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_slut_users', function (Blueprint $table) {
            $table->dropColumn("absense_file");
        });
    }
};

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
        Schema::table('reading_station_users', function (Blueprint $table) {
            $table->string('consultant', 256)->nullable()->after('last_weekly_program');
            $table->string('representative', 256)->nullable()->after('consultant');
            $table->date('contract_start')->nullable()->after('representative');
            $table->date('contract_end')->nullable()->after('contract_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_users', function (Blueprint $table) {
            $table->dropColumn('consultant');
            $table->dropColumn('representative');
            $table->dropColumn('contract_start');
            $table->dropColumn('contract_end');
        });
    }
};

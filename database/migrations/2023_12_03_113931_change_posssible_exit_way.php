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
            $table->dropColumn('posssible_exit_way');
            $table->enum('possible_exit_way', ['taxi','mother','father','relatives','parents_notified','tillnight','self'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_absent_presents', function (Blueprint $table) {
            $table->dropColumn('possible_exit_way');
            $table->enum('posssible_exit_way', ['taxi','mother','father','relatives','parents_notified','tillnight','self'])->nullable();
        });
    }
};

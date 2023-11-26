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
        DB::statement("ALTER TABLE reading_station_absent_presents MODIFY COLUMN posssible_exit_way ENUM('taxi','mother','father','relatives','parents_notified','tillnight','self')");
        DB::statement("ALTER TABLE reading_station_absent_presents MODIFY COLUMN exit_way ENUM('taxi','mother','father','relatives','parents_notified', 'self')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE reading_station_absent_presents MODIFY COLUMN posssible_exit_way ENUM('taxi','mother','father','relatives','parents_notified','tillnight')");
        DB::statement("ALTER TABLE reading_station_absent_presents MODIFY COLUMN exit_way ENUM('taxi','mother','father','relatives','parents_notified')");
    }
};

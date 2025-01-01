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
        DB::statement("ALTER TABLE reading_station_users ADD INDEX (reading_station_id)");
        DB::statement("ALTER TABLE reading_station_users ADD INDEX (user_id)");
        DB::statement("ALTER TABLE reading_station_users ADD INDEX (`status`)");
        DB::statement("ALTER TABLE reading_station_weekly_programs ADD INDEX (reading_station_user_id)");
        DB::statement("ALTER TABLE reading_station_weekly_programs ADD INDEX (`start`)");
        DB::statement("ALTER TABLE reading_station_weekly_programs ADD INDEX (`end`)");
        DB::statement("ALTER TABLE reading_station_slut_users ADD INDEX (reading_station_weekly_program_id)");
        DB::statement("ALTER TABLE reading_station_slut_users ADD INDEX (reading_station_slut_id)");
        DB::statement("ALTER TABLE reading_station_slut_users ADD INDEX (`day`)");
        DB::statement("ALTER TABLE reading_station_slut_users ADD INDEX (is_required)");
        DB::statement("ALTER TABLE reading_station_slut_users ADD INDEX (`status`)");
        DB::statement("ALTER TABLE reading_station_sluts ADD INDEX (reading_station_id)");
        DB::statement("ALTER TABLE reading_station_sluts ADD INDEX (`start`)");
        DB::statement("ALTER TABLE reading_station_sluts ADD INDEX (`end`)");
        DB::statement("ALTER TABLE reading_station_absent_presents ADD INDEX (user_id)");
        DB::statement("ALTER TABLE reading_station_absent_presents ADD INDEX (reading_station_id)");
        DB::statement("ALTER TABLE reading_station_absent_presents ADD INDEX (`day`)");
        DB::statement("ALTER TABLE reading_station_absent_presents ADD INDEX (is_processed)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

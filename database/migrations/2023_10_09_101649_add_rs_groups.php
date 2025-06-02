<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRsGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now()->toDateString();
        DB::table('groups')->insert([
            [
                "name" => "Reading Station Admin",
                "type" => "admin_reading_station",
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "name" => "Reading Station Branch Admin",
                "type" => "admin_reading_station_branch",
                "created_at" => $now,
                "updated_at" => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('groups')->whereIn("type", ["admin_reading_station", "admin_reading_station_branch"])->delete();
    }
}

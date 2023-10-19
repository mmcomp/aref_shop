<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReadingStationGate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group_ids = DB::table('groups')->whereIn('type', ['admin', 'admin_reading_station', 'admin_reading_station_branch'])->pluck('id');
        if (count($group_ids) > 0) {
            $query = [];
            foreach ($group_ids as $group_id) {
                $query[] = [
                    "groups_id" => $group_id,
                    "key" => "reading_station",
                    "users_id" => 1,
                    "created_at" => Carbon::now()->toDateString(),
                    "updated_at" => Carbon::now()->toDateString(),
                ];
            }
            DB::table('group_gates')->insert($query);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('group_gates')->where('key', 'reading_station')->delete();
    }
}

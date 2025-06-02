<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $found = DB::table('group_gates')->where("key", "reading_station_report")->first();
        if (!$found) {
            $group_ids = DB::table('groups')->whereIn('type', ['admin', 'admin_reading_station'])->pluck('id');
            if (count($group_ids) > 0) {
                $query = [];
                foreach ($group_ids as $group_id) {
                    $query[] = [
                        "groups_id" => $group_id,
                        "key" => "reading_station_report",
                        "users_id" => 1,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ];
                }
                DB::table('group_gates')->insert($query);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('group_gates')->where("key", "reading_station_report")->delete();
    }
};

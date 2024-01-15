<?php

use Carbon\Carbon;
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
        $found = DB::table('group_gates')->where("key", "reading_station_student")->first();
        if (!$found) {
            $group_ids = DB::table('groups')->where('type', 'user')->pluck('id');
            if (count($group_ids) > 0) {
                $query = [];
                foreach ($group_ids as $group_id) {
                    $query[] = [
                        "groups_id" => $group_id,
                        "key" => "reading_station_student",
                        "users_id" => 1,
                        "created_at" => Carbon::now()->toDateTimeString(),
                        "updated_at" => Carbon::now()->toDateTimeString(),
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
        DB::table('group_gates')->where("key", "reading_station_student")->delete();
    }
};

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
        DB::table('groups')->insert([
            "name" => "Reading Station Branch User",
            "type" => "user_reading_station_branch",
            "created_at" => Carbon::now()->toDateString(),
            "updated_at" => Carbon::now()->toDateString(),

        ]);
        $group = DB::table('groups')->where('type', 'user_reading_station_branch')->first();
        if (!$group) {
            DB::table('groups')->insert([
                "name" => "Reading Station Branch User",
                "type" => "user_reading_station_branch",
                "created_at" => Carbon::now()->toDateString(),
                "updated_at" => Carbon::now()->toDateString(),

            ]);
            $group = DB::table('groups')->where('type', 'user_reading_station_branch')->first();
        }
        DB::table('group_gates')->insert([
            [
                "groups_id" => $group->id,
                "key" => "ping",
                "users_id" => 1,
                "created_at" => Carbon::now()->toDateString(),
                "updated_at" => Carbon::now()->toDateString(),
            ],
            [
                "groups_id" => $group->id,
                "key" => "reading_station",
                "users_id" => 1,
                "created_at" => Carbon::now()->toDateString(),
                "updated_at" => Carbon::now()->toDateString(),
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $group = DB::table('groups')->where('type', 'user_reading_station_branch')->first();
        if ($group) {
            DB::table('group_gates')->where('groups_id', $group->id)->delete();
            DB::table('groups')->where('type', 'user_reading_station_branch')->delete();
        }
    }
};

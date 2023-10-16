<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRsMenuGroup extends Migration
{
    public $menuGroups = [
        ["50", "1", "113", "1", NULL, NULL, "0"],
        ["51", "1", "114", "1", NULL, NULL, "0"],
        ["52", "1", "115", "1", NULL, NULL, "0"],
        ["53", "1", "116", "1", NULL, NULL, "0"],
        ["54", "1", "117", "1", NULL, NULL, "0"],
        ["55", "1", "118", "1", NULL, NULL, "0"],
        ["56", "1", "119", "1", NULL, NULL, "0"],
        ["57", "1", "120", "1", NULL, NULL, "0"],
        ["58", "1", "121", "1", NULL, NULL, "0"],
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = [];
        foreach($this->menuGroups as $menuGroup) {
            $query[] = [
                "id" => $menuGroup[0],
                "groups_id" => $menuGroup[1],
                "menus_id" => $menuGroup[2],
                "users_id" => $menuGroup[3],
                "created_at" => $menuGroup[4],
                "updated_at" => $menuGroup[5],
                "is_deleted" => $menuGroup[6],
            ];
        }
        DB::table("group_menus")->insert($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $ids = [];
        foreach($this->menuGroups as $menuGroup) {
            $ids[] = $menuGroup[0];
        }
        DB::table("group_menus")->whereIn("id", $ids)->delete();
    }
}

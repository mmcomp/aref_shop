<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRsMenuDetails extends Migration
{
    public $menus = [
        ["109","dropdown","عملیات پایه سالنهای مطالعه",null,"AddCircleIcon","#","2022-01-11 13:04:24","2022-01-11 13:04:24",null,"0"],
        ["110","link","تعریف شعبه",null,"AddCircleIcon","/reading-station/branches","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["111","link","تعریف علتهای غیبت",null,"AddCircleIcon","/reading-station/absent-reasons","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["112","link","تعریف تخلفات",null,"AddCircleIcon","/reading-station/strikes","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["113","link","جدول ستاره",null,"AddCircleIcon","/reading-station/packages","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["114","link","تعریف روزهای تعطیل",null,"AddCircleIcon","/reading-station/offdays","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["115","link","تعریف پرسنل",null,"AddCircleIcon","/reading-station/users","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["116","link","تعریف زنگ",null,"DoorbellIcon","/reading-station/bells","2022-01-11 13:04:24","2022-01-11 13:04:24","109","0"],
        ["117","dropdown","مدیریت دانش آموزان",null,"AddCircleIcon","#","2022-01-11 13:04:24","2022-01-11 13:04:24",null,"0"],
        ["118","link","فهرست دانش آموزان",null,"AddCircleIcon","/reading-station/students","2022-01-11 13:04:24","2022-01-11 13:04:24","117","0"],
        ["119","link","جابجایی دانش آموزان",null,"AddCircleIcon","/reading-station/transfer","2022-01-11 13:04:24","2022-01-11 13:04:24","117","0"],
        ["120","link","موجه کردن غیبتها",null,"AddCircleIcon","/reading-station/absence-reasons","2022-01-11 13:04:24","2022-01-11 13:04:24","117","0"],
        ["121","link","ارسال پیامک",null,"AddCircleIcon","/reading-station/send-sms","2022-01-11 13:04:24","2022-01-11 13:04:24","117","0"],
    ];

    public $menuGroups = [
        ["46","1","109","1",null,null,"0"],
        ["47","1","110","1",null,null,"0"],
        ["48","1","111","1",null,null,"0"],
        ["49","1","112","1",null,null,"0"],
        ["50","1","113","1",null,null,"0"],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = [];
        foreach($this->menus as $menu) {
            $query[] = [
                "id" => $menu[0],
                "slug" => $menu[1],
                "name" => $menu[2],
                "icon" => $menu[3],
                "mui_icon" => $menu[4],
                "href" => $menu[5],
                "created_at" => $menu[6],
                "updated_at" => $menu[7],
                "parent_id" => $menu[8],
                "is_deleted" => $menu[9],
            ];
        }
        DB::table("menus")->insert($query);
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
        foreach($this->menus as $menu) {
            $ids[] = $menu[0];
        }
        DB::table("menus")->whereIn("id", $ids)->delete();
        $ids = [];
        foreach($this->menuGroups as $menuGroup) {
            $ids[] = $menuGroup[0];
        }
        DB::table("group_menus")->whereIn("id", $ids)->delete();
    }
}

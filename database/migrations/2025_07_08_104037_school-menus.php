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
        $schoolAdminGroupId = DB::table('groups')->where('type', 'school-admin')->first()->id;
        $menus = DB::table('menus')->whereIn('href', [
            '/admin/user',
            '/admin/listusers',
            '/admin/adduser',
            '/admin/product',
            '/admin/listproducts',
            '/admin/discount',
            '/admin/listcoupons',
            '/admin/coupons/add',
            '/admin/addorder',
            '/admin/reports',
        ])->get();
        foreach ($menus as $menu) {
            DB::table('group_menus')->insert([
                'groups_id' => $schoolAdminGroupId,
                'menus_id' => $menu->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schoolAdminGroupId = DB::table('groups')->where('type', 'school-admin')->first()->id;
        DB::table('group_menus')->where('groups_id', $schoolAdminGroupId)->delete();
    }
};

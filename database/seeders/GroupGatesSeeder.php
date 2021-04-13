<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupGatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $arr = ['city', 'group', 'product', 'productDetailChair', 'productDetailDownload', 'productDetailPackage', 'productDetailVideo',
            'province', 'user', 'category-one', 'category-two', 'category-three', 'group_gate'];
        for ($i = 0; $i < count($arr) ; $i++) {
            DB::table('group_gates')->insert([
                'groups_id' => 1,
                'users_id' => 1,
                'key' => $arr[$i],
            ]);
        }
    }
}

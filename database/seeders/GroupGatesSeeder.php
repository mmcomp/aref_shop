<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GroupGatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $arr = [
            'city', 'group', 'product', 'productDetailChair', 'productDetailDownload', 'productDetailPackage', 'productDetailVideo',
            'province', 'user', 'category-one', 'category-two', 'category-three', 'group_gate', 'coupon', 'video-session', 'file', 'product-file', 'video-session-file','product-comment-admin', 'user-description', 'admin-order', 'report-sale','user-video-session-admin'
        ];
        for ($i = 0; $i < count($arr); $i++) {
            DB::table('group_gates')->insert([
                'groups_id' => 1,
                'users_id' => 1,
                'key' => $arr[$i],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }
        $keys = ['cart', 'product-of-user','videosessions-of-user', 'payment', 'product-detail-video-of-user', 'product-packages-of-user','user-product','product-comment', 'order', 'sessions','attach-homework', 'user-description-for-user'];
        for ($i = 0; $i < count($keys); $i++) {
            DB::table('group_gates')->insert([
                'groups_id' => 2,
                'users_id' => 1,
                'key' => $keys[$i],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }
        DB::table('group_gates')->insert([
            'groups_id' => 3,
            'users_id' => 1,
            'key' => 'user-description-for-teacher',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

    }
}

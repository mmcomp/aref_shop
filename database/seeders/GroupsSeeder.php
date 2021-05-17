<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = ['Admin', 'Registered'];
        $types = ['admin', 'user'];
        for ($i = 0; $i < 2; $i++) {
            DB::table('groups')->insert([
                'name' => $names[$i],
                'type' => $types[$i],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}

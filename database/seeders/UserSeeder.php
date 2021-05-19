<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emails = ['09223466069', '09370972142', '09223145432'];
        $first_names = ['maryam', 'user', 'user'];
        $last_names = ['khodaparast', 'test', 'test1'];
        $groups = [1, 2, 2];
        for ($i = 0; $i < 3; $i++) {
            DB::table('users')->insert([
                'email' => $emails[$i],
                'first_name' => $first_names[$i],
                'last_name' => $last_names[$i],
                'groups_id' => $groups[$i],
                'pass_txt' => '123456',
                'password' => bcrypt('123456'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }
    }
}

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
        DB::table('users')->insert([
            'email' => '09223466069',
            'first_name' => 'maryam',
            'last_name' => 'khodaparast',
            'groups_id' => 1,
            'pass_txt' => '123456',
            'password' => bcrypt('123456'),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')    
        ]);
        DB::table('users')->insert([
            'email' => '09370972142',
            'first_name' => 'user',
            'last_name' => 'test',
            'groups_id' => 2,
            'pass_txt' => '123456',
            'password' => bcrypt('123456'),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')    
        ]);
    }
}

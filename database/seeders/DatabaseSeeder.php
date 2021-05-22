<?php

namespace Database\Seeders;

use Database\Seeders\GroupGatesSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\GroupsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    protected $toTruncate = ['group_gates', 'users', 'groups', 'menus', 'group_menus'];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        foreach($this->toTruncate as $table) {
            DB::table($table)->truncate();
        }
        $this->call(GroupGatesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(GroupsSeeder::class);
        $this->call(GroupMenusSeeder::class);
        $this->call(MenusSeeder::class);


        Model::reguard();
    }
}

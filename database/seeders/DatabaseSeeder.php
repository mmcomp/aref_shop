<?php

namespace Database\Seeders;

use Database\Seeders\GroupGatesSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\GroupsSeeder;
use Database\Seeders\CitiesSeeder;
use Database\Seeders\ProvincesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    protected $toTruncate = ['group_gates', 'groups', 'menus', 'group_menus', 'cities', 'provinces'];

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
        $this->call(UsersSeeder::class);
        $this->call(GroupGatesSeeder::class);
        $this->call(GroupsSeeder::class);
        $this->call(GroupMenusSeeder::class);
        $this->call(MenusSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(ProvincesSeeder::class);

        Model::reguard();
    }
}

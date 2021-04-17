<?php

namespace Database\Seeders;

use Database\Seeders\GroupGatesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    protected $toTruncate = ['group_gates'];

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

        Model::reguard();
    }
}

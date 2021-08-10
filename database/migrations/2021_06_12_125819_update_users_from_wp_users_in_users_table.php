<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class UpdateUsersFromWpUsersInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // Migrate from WP Users > Laravel Users
         if (Schema::hasTable('divi_users')) {
            DB::table('divi_users')
            ->orderBy('id')
            ->chunk(100, function ($wp_users) {
                foreach ($wp_users as $wp_user) {

                    // add new user (if doesn't exist)
                    User::firstOrCreate(
                        [
                            'email' => $wp_user->user_login,
                        ],
                        [
                            'email' => $wp_user->user_login,
                            'last_name' => $wp_user->user_nicename,
                            'password' => $wp_user->user_pass
                        ]
                    );
                }
            });
         }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

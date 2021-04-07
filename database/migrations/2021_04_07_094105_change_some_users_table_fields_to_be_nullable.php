<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSomeUsersTableFieldsToBeNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer("referrer_users_id")->default(null)->nullable()->change();
            $table->integer("cities_id")->default(null)->nullable()->change();
            $table->integer("groups_id")->default(2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer("referrer_users_id")->default(0)->nullable(false)->change();
            $table->integer("cities_id")->default(0)->nullable(false)->change();
            $table->integer("groups_id")->default(0)->nullable()->change();
        });
    }
}

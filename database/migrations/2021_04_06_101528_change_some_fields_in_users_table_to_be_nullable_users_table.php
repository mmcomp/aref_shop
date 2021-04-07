<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSomeFieldsInUsersTableToBeNullableUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer("referrer_users_id")->default(0)->change();
            $table->string("address")->nullable()->change();
            $table->string("postall")->nullable()->change();
            $table->integer("cities_id")->default(0)->change();
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
            $table->integer("referrer_users_id")->default(null)->change();
            $table->string("address")->nullable(false)->change();
            $table->string("postall")->nullable(false)->change();
            $table->integer("cities_id")->default(null)->change();
        });
    }
}

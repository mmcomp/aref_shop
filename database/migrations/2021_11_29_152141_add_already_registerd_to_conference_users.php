<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlreadyRegisterdToConferenceUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conference_users', function (Blueprint $table) {
            $table->enum('already_registerd', ['yes', 'no'])->default('no')->after('referrer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conference_users', function (Blueprint $table) {
            $table->dropColumn('already_registerd');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSmsValidationsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE `sms_validations` CHANGE `type` `type` ENUM('register','forget_pass','login') CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_validations', function (Blueprint $table) {
            //
        });
    }
}

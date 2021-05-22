<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSmsValidationChangeMobileTypeAddId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_validations', function (Blueprint $table) {
            $table->string('mobile')->unique()->change();
            $table->dropPrimary();
        });
        Schema::table('sms_validations', function (Blueprint $table) {
            $table->increments('id')->first();
        });
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToSmsValidations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_validations', function (Blueprint $table) {
            $table->integer("product_detail_videos_id")->nullable()->after('type'); //
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
            $table->dropColumn("product_detail_videos_id");
        });
    }
}

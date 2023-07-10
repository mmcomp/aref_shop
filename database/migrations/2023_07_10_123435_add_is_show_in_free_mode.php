<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsShowInFreeMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_sessions', function (Blueprint $table) {
            $table->boolean('show_in_zero_price')->after('price')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_sessions', function (Blueprint $table) {
            $table->dropColumn('show_in_zero_price');

        });
    }
}

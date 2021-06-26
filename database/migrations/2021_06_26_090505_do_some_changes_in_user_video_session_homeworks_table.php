<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DoSomeChangesInUserVideoSessionHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_video_session_homeworks', function (Blueprint $table) {
            $table->integer('user_video_sessions_id')->unsigned()->default(0)->change();
            $table->string('file', 255)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_video_session_homeworks', function (Blueprint $table) {
            $table->integer('user_video_sessions_id')->unsigned()->default(null)->change();
            $table->string('file', 255)->nullable()->change();
        });
    }
}

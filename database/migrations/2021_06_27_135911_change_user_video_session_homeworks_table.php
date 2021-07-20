<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserVideoSessionHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_video_session_homeworks', function (Blueprint $table) {
            $table->dropColumn('teacher_description');
            $table->integer('teachers_users_id')->unsigned()->default(0)->after('file');
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
            $table->text('teacher_description')->nullable()->after('file');
            $table->dropColumn('teachers_users_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EdituserVideoSessionHomeworksFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_video_session_homeworks', function (Blueprint $table) {
            $table->enum('teacher_confirmation',['noAction','seen'])->default('noAction')->after('description')->nullable();
            $table->enum('teacher_status',['confirmed','semi_confirmed', 'failed'])->after('description')->nullable();
            $table->String('teacher_description')->after('description')->nullable();

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
            $table->dropColumn('teacher_confirmation');
            $table->dropColumn('teacher_status');
            $table->dropColumn('teacher_description');
        });
    }
}

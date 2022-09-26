<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsToBeIndexInUserVideoSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->index('video_sessions_id');
            $table->index('users_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->dropIndex(['video_sessions_id']);
            $table->dropIndex(['users_id']);
        });
    }
}

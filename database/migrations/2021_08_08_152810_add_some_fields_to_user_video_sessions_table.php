<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToUserVideoSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->timestamp('online_started_at')->nullable()->after('video_sessions_id');
            $table->timestamp('online_exited_at')->nullable()->after('video_sessions_id');
            $table->integer('online_spend')->unsigned()->after('video_sessions_id')->default(0);
            $table->timestamp('offline_started_at')->nullable()->after('video_sessions_id');
            $table->timestamp('offline_exited_at')->nullable()->after('video_sessions_id');
            $table->integer('offline_spend')->unsigned()->after('video_sessions_id')->default(0);

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
            $table->dropColumn('online_started_at');
            $table->dropColumn('online_exited_at');
            $table->dropColumn('online_spend');
            $table->dropColumn('offline_started_at');
            $table->dropColumn('offline_exited_at');
            $table->dropColumn('offline_spend');

        });
    }
}

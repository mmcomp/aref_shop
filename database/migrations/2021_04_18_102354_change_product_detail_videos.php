<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductDetailVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("start_date");
            $table->dropColumn("start_time");
            $table->dropColumn("end_time");
            $table->dropColumn("teacher_users_id");
            $table->dropColumn("video_session_type");
            $table->dropColumn("video_link");
            $table->dropColumn("is_hidden");
            $table->integer("video_sessions_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            //
        });
    }
}

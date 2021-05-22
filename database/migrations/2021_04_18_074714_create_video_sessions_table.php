<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_sessions', function (Blueprint $table) {
            $table->id();
            $table->string("name", 255);
            $table->date("start_date");
            $table->time("start_time");
            $table->time("end_time");
            $table->integer("teacher_users_id")->nullable();
            $table->integer("price");
            $table->enum("video_session_type", ['online', 'offline']);
            $table->text("video_link");
            $table->boolean("is_hidden")->default(false);
            $table->boolean("is_deleted")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_sessions');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVideoSessionPresentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_video_session_presents', function (Blueprint $table) {
            $table->id();
            $table->integer('users_id')->index();
            $table->integer('video_sessions_id')->index();
            $table->timestamp('online_started_at')->nullable();
            $table->timestamp('online_exited_at')->nullable();
            $table->integer('online_spend')->unsigned()->default(0);
            $table->timestamp('offline_started_at')->nullable();
            $table->timestamp('offline_exited_at')->nullable();
            $table->integer('offline_spend')->unsigned()->default(0);

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
        Schema::dropIfExists('user_video_session_presents');
    }
}

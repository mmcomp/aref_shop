<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVideoSessionHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_video_session_homeworks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_video_sessions_id')->unsigned();
            $table->string('file', 255)->nullable();
            $table->text('teacher_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_deleted')->default(false);
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
        Schema::dropIfExists('user_video_session_homeworks');
    }
}

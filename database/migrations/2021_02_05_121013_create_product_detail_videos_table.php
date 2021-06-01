<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDetailVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_detail_videos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->date('start_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('teacher_users_id')->unsigned()->nullable();
            $table->integer('products_id')->unsigned();
            $table->integer('price')->unsigned();
            $table->enum('video_session_type', ['online', 'offline']);
            $table->text('video_link');
            $table->boolean('is_hidden')->default(0);
            $table->boolean('is_deleted')->default(0);
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
        Schema::dropIfExists('product_detail_videos');
    }
}

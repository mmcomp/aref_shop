<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDetailVideoChildsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_detail_video_childs', function (Blueprint $table) {
            $table->id();
            $table->integer('product_detail_videos_id')->unsigned();
            $table->integer('product_detail_videos_childs_id')->unsigned();
            $table->integer('saver_users_id')->unsigned();
            
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
        Schema::dropIfExists('product_detail_video_childs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('short_description', 1500);
            $table->text('long_description');
            $table->integer('price')->unsigned()->default(0);
            $table->integer('sale_price')->unsigned()->nullable();
            $table->date('sale_expire')->nullable();
            $table->text('video_props');
            $table->integer('category_ones_id')->unsigned()->nullable();
            $table->integer('category_twos_id')->unsigned()->nullable();
            $table->integer('category_threes_id')->unsigned()->nullable();
            $table->string('main_image_path', 1000);
            $table->string('main_image_thumb_path', 1000);
            $table->string('second_image_path', 1000);
            $table->boolean('is_deleted')->default(0);
            $table->boolean('published')->default(0);
            $table->enum('type', ['normal','download','chairs','video'])->default('normal');
            
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
        Schema::dropIfExists('products');
    }
}

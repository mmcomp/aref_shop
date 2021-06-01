<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('orders_id')->unsigned();
            $table->integer('products_id')->unsigned();
            $table->integer('price')->unsigned();
            $table->integer('coupons_id')->unsigned();
            $table->integer('coupons_amount')->unsigned();
            $table->enum('coupons_type', ['register', 'forget_pass']);
            $table->integer('users_id')->unsigned();
            $table->boolean('all_videos_buy')->default(0);
            $table->enum('status', ['ok', 'waiting', 'cancel']);
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
        Schema::dropIfExists('order_details');
    }
}

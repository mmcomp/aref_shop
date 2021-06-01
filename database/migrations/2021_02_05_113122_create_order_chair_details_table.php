<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderChairDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_chair_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_details_id')->unsigned();
            $table->integer('chair_number');
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
        Schema::dropIfExists('order_chair_details');
    }
}

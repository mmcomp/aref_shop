<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TeamProductDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_product_defaults', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('user_id_creator');
            //$table->foreignId('team_user_id');
            $table->string('product_id');
            //$table->boolean('is_full');            
            $table->timestamps();
            $table->softDeletes();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_product_defaults');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationUserStrikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_user_strikes', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_slut_user_id');
            $table->integer('reading_station_strike_id')->nullable();
            $table->integer('reading_station_strike_score');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('reading_station_user_strikes');
    }
}

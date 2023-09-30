<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationSlutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_sluts', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_id');
            $table->string('name');
            $table->time('start')->default('7:45');
            $table->time('end')->default('9:15');
            $table->integer('duration')->default(90);
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
        Schema::dropIfExists('reading_station_sluts');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_calls', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_slut_user_id');
            $table->enum('reason', ['entry', 'exit', 'latency', 'absence']);
            $table->boolean('answered')->default(false);
            $table->string('description')->nullable();
            $table->integer('caller_user_id');
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
        Schema::dropIfExists('reading_station_calls');
    }
}

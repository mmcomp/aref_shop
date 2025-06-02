<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationWeeklyProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_weekly_programs', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_user_id');
            $table->string('name');
            $table->integer('required_time');
            $table->integer('optional_time');
            $table->boolean('is_verified')->default(false);
            $table->date('start');
            $table->date('end');
            $table->integer('required_time_done')->default(0);
            $table->integer('optional_time_done')->default(0);
            $table->integer('strikes_done')->default(0);
            $table->integer('absence_done')->default(0);
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
        Schema::dropIfExists('reading_station_weekly_programs');
    }
}

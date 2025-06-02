<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationAbsentPresentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_absent_presents', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('reading_station_id');
            $table->date('day');
            $table->integer('reading_station_slut_user_exit_id')->nullable();
            $table->time('possible_end')->nullable();
            $table->time('end')->nullable();
            $table->enum('posssible_exit_way', ['taxi', 'mother', 'father', 'relatives', 'parents_notified', 'till_night'])->nullable();
            $table->enum('exit_way', ['taxi', 'mother', 'father', 'relatives', 'parents_notified'])->nullable();
            $table->enum('enter_way', ['mother', 'father', 'relatives'])->nullable();
            $table->string('attachment_address')->nullable();
            $table->boolean('is_optional_visit')->default(false);
            $table->boolean('is_processed')->default(false);
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
        Schema::dropIfExists('reading_station_absent_presents');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStationSlutUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_station_slut_users', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_weekly_program_id');
            $table->integer('reading_station_slut_id');
            $table->date('day');
            $table->boolean('is_required')->default(true);
            $table->enum('status', ['defined', 'absent', 'present', 'late_15', 'late_30', 'late_45', 'late_60', 'late_60_plus', 'sleep'])->default('defined');
            $table->integer('reading_station_absent_reason_id')->nullable();
            $table->integer('reading_station_absent_reason_score')->default(0);
            $table->enum('absense_approved_status', ['not_approved', 'semi_approved', 'approved'])->default('not_approved');
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
        Schema::dropIfExists('reading_station_slut_users');
    }
}

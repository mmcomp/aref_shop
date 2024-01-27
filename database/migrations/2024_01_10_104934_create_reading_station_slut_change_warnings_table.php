<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reading_station_slut_change_warnings', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_station_slut_user_id');
            $table->string('description', 255);
            $table->integer('operator_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('reader_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_station_slut_change_warnings');
    }
};

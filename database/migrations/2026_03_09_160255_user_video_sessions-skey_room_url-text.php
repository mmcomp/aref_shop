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
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->text("sky_room_url")->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->string("sky_room_url", 255)->change()->nullable();
        });
    }
};

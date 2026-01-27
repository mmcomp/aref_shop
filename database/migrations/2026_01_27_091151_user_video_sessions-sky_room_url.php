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
            $table->string("sky_room_url")->nullable()->after("video_sessions_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_video_sessions', function (Blueprint $table) {
            $table->dropColumn("sky_room_url");
        });
    }
};

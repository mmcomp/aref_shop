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
        Schema::table('sky_rooms', function (Blueprint $table) {
            $table->renameColumn("room)id", "room_id");
            $table->string("url")->nullable()->after("max_users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sky_rooms', function (Blueprint $table) {
            $table->renameColumn("room_id", "room)id");
            $table->dropColumn("url");
        });
    }
};

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
            // Note: renameColumn is not always supported reliably in MariaDB via Laravel Schema builder.
            // The recommended way is to use a separate migration for renaming the column or use a raw SQL statement.
            // Here is a version that works for MariaDB using raw SQL for renaming:
            DB::statement('ALTER TABLE sky_rooms CHANGE `room)id` `room_id` INT');
            $table->string("url")->nullable()->after("max_users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sky_rooms', function (Blueprint $table) {
            DB::statement('ALTER TABLE sky_rooms CHANGE `room_id` `room)id` INT');
            $table->dropColumn("url");
        });
    }
};

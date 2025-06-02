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
        Schema::table('reading_station_users', function (Blueprint $table) {
            $table->enum('status', ['active', 'canceled', 'relocated'])->after('contract_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_station_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

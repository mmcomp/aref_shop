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
        Schema::table('group_menus', function (Blueprint $table) {
            $table->integer('order_id')->nullable()->after('users_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_menus', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};

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
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->boolean('free_hidden')->default(false)->after('is_hidden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_detail_videos', function (Blueprint $table) {
            $table->dropColumn('free_hidden');
        });
    }
};

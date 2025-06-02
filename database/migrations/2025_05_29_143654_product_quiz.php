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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['normal','download','chairs','video','package','quiz24'])->nullable()->change();
            $table->json('quiz24_data')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['normal','download','chairs','video','package'])->nullable()->change();
            $table->dropColumn('quiz24_data');
        });
    }
};

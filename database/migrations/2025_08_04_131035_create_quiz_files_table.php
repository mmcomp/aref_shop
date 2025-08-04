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
        Schema::create('quiz_files', function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_id');
            $table->integer('file_id');
            $table->string('title');
            $table->string('url');
            $table->string('access_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_files');
    }
};

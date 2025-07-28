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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_id');
            $table->integer('examCode');
            $table->integer('questionCount');
            $table->string('fileName')->nullable();
            $table->string('questionfileName')->nullable();
            $table->string('answerfileName')->nullable();
            $table->string('keyfileName')->nullable();
            $table->string('entryDate')->nullable();
            $table->string('startDate')->nullable();
            $table->integer('examTime')->nullable();
            $table->string('startDateGregorian')->nullable();
            $table->string('endDateGregorian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

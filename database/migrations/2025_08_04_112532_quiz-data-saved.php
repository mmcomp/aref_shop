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
        Schema::table('user_quizzes', function (Blueprint $table) {
            $table->integer('Correct')->nullable()->after('quiz_id');
            $table->integer('Wrong')->nullable()->after('Correct');
            $table->integer('NoAnswer')->nullable()->after('Wrong');
            $table->integer('Balance')->nullable()->after('NoAnswer');
            $table->integer('BestBalance')->nullable()->after('Balance');
            $table->integer('BalanceAvg')->nullable()->after('BestBalance');
            $table->double('Score')->nullable()->change();
            $table->double('BestScore')->nullable()->after('Score');
            $table->double('ScoreAvg')->nullable()->after('BestScore');
            $table->integer('Rank')->nullable()->after('ScoreAvg');
            $table->integer('TotalCount')->nullable()->after('Rank');
            $table->double('CorrectAvg')->nullable()->after('TotalCount');
            $table->double('WrongAvg')->nullable()->after('CorrectAvg');
            $table->double('NoAnswerAvg')->nullable()->after('WrongAvg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_quizzes', function (Blueprint $table) {
            $table->dropColumn('Correct');
            $table->dropColumn('Wrong');
            $table->dropColumn('NoAnswer');
            $table->dropColumn('Balance');
            $table->dropColumn('BestBalance');
            $table->dropColumn('BalanceAvg');
            $table->integer('Score')->nullable()->change();
            $table->dropColumn('BestScore');
            $table->dropColumn('ScoreAvg');
            $table->dropColumn('Rank');
            $table->dropColumn('TotalCount');
            $table->dropColumn('CorrectAvg');
            $table->dropColumn('WrongAvg');
            $table->dropColumn('NoAnswerAvg');
        });
    }
};

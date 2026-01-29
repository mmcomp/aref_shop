<?php

use App\Models\Product;
use App\Models\ProductQuiz;
use App\Models\Quiz;
use App\Utils\Quiz24Service;
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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('title')->nullable()->after('examCode');
        });
        
        $webQuizzes = Quiz24Service::getAllExams();
        foreach ($webQuizzes['exams'] as $webQuiz) {
            (new Quiz())->fromQuiz($webQuiz);
        }
        $exams = Quiz::all();
        $quizProducts = Product::where('type', 'quiz24')->get();
        foreach ($quizProducts as $quizProduct) {
            $quizData = json_decode($quizProduct->quiz24_data, true);
            foreach ($quizData as $quiz) {
                $quiz = $exams->where('exam_id', $quiz)->first();
                if ($quiz) {
                    $productQuiz = new ProductQuiz();
                    $productQuiz->product_id = $quizProduct->id;
                    $productQuiz->quiz_id = $quiz->id;
                    $productQuiz->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

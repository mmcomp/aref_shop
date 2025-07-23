<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Utils\Quiz24Service;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /*
                "id": 2312258,
            "examCode": 2269156,
            "title": "زیست شبیه ساز کنکور - 21 تیرماه 1404",
            "questionCount": 45,
            "fileName": null,
            "questionfileName": null,
            "answerfileName": null,
            "keyfileName": null,
            "entryDate": "1404/04/16",
            "startDate": "1404/04/21 20:15",
            "endDate": "1404/05/01 20:40",
            "examTime": 45,
            "startDateGregorian": "2025-07-12T16:45:00.000000Z",
            "endDateGregorian": "2025-07-23T17:10:00.000000Z",
            "inNextMonth": false
    */
    public function index()
    {
        $exams = Quiz24Service::getAllExams();
        $freeQuizProducts = Product::where('is_deleted', false)
            ->where('published', true)
            ->where('type', 'quiz24')
            ->where('sale_price', 0)
            ->get();
        $todayFreeQuizProducts = [];
        foreach ($freeQuizProducts as $product) {
            $productQuiz = json_decode($product->quiz24);
            foreach ($productQuiz as $quiz) {
                foreach ($exams['exams'] as $exam) {
                    if ($exam->id == $quiz && Carbon::parse($exam->startDateGregorian)->isToday()) {
                        $todayFreeQuizProducts[] = $product;
                    }
                }
            }
        }

        $user = auth()->user();
        $userQuizProducts = Product::where('is_deleted', false)
            ->where('published', true)
            ->where('type', 'quiz24')
            ->whereHas('userProducts', function ($query) use ($user) {
                $query->where('users_id', $user->id);
            })
            ->get();
        $todayUserQuizProducts = [];
        foreach ($userQuizProducts as $product) {
            $productQuiz = json_decode($product->quiz24);
            foreach ($productQuiz as $quiz) {
                foreach ($exams['exams'] as $exam) {
                    if ($exam->id == $quiz && Carbon::parse($exam->startDateGregorian)->isToday()) {
                        $todayUserQuizProducts[] = $product;
                    }
                }
            }
        }


        return response()->json(['todayFreeQuizProducts' => $todayFreeQuizProducts, 'todayUserQuizProducts' => $todayUserQuizProducts]);
    }
}

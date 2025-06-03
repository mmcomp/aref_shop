<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Quiz24Service
{
    static function getSchools()
    {
        $req = [
            "pageIndex" => 1,
            "pageSize" => 50
        ];
        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "users", $req);

        $res = $response->json();
        Log::info('Quiz24Service getSchools response', ['response' => $res]);
        $userId = 0;
        if ($res['totalCount'] > 0) {
            $userId = $res['result'][0]['userId'];
        }

        return $userId;
    }

    static function registerStudent(array $userDate)
    {
        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "registerStudent", $userDate);

        $res = $response->json();

        return $res;
    }


    static function getExams()
    {
        $req = [
            "userId" => env('QUIZ24_SCHOOL_ID', 3516802),
            "pageIndex" => 1,
            "pageSize" => 50
        ];
        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "exams", $req);
        $exams = [];
        $res = $response->json();
        Log::info('Quiz24Service getExams response', ['response' => $res]);
        if (isset($res['totalCount']) && $res['totalCount'] > 0) {
            $exams = $res['result'];
        }
        return compact('exams');
    }
}

// 3515012 hamed

// class Quiz24Student
// {
//     public $userId;
//     public $userName;
//     public $name;
//     public $family;
//     public $password;
//     public $isActive;
//     public $classCode;
// }

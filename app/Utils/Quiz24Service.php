<?php

namespace App\Utils;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

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
        $userDate["userId"] = env('QUIZ24_SCHOOL_ID', 3525433);
        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "registerStudent", $userDate);

        $res = $response->json();

        return $res;
    }

    static function getExams(int $page)
    {
        $req = [
            "userId" => env('QUIZ24_SCHOOL_ID', 3525433),
            "pageIndex" => $page,
            "pageSize" => 50
        ];

        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "exams", $req);
        $exams = [];
        $res = $response->json();
        Log::info('Quiz24Service getExams response', ['response' => $res]);
        $totalCount = 0;
        if (isset($res['totalCount']) && $res['totalCount'] > 0) {
            $totalCount = $res['totalCount'];
            $exams = $res['result'];
        }
        return compact('exams', 'totalCount');
    }

    static function getAllExams()
    {
        $result = [];
        $page = 1;
        while (true) {
            $res = self::getExams($page);
            $result = array_merge($result, $res['exams']);
            if ($res['totalCount'] <= $page * 50) {
                break;
            }
            $page++;
        }

        $finalResult = [];

        foreach ($result as $i => $exam) {
            $result[$i]['startDateGregorian'] = Jalalian::fromFormat('Y/m/d H:i', $exam['startDate'])->toCarbon();
            $result[$i]['endDateGregorian'] = Jalalian::fromFormat('Y/m/d H:i', $exam['endDate'])->toCarbon();
            if ($result[$i]['startDateGregorian']->isPast() || $result[$i]['endDateGregorian']->isAfter(Carbon::now()->addMonth(1))) {
                continue;
            }
            $finalResult[] = $result[$i];
        }

        return ["exams" => $finalResult, "totalCount" => count($finalResult)];
    }

    static function getExamForAUser($userName, $examCode)
    {
        $req = [
            "userId" => env('QUIZ24_SCHOOL_ID', 3525433),
            "userName" => $userName,
            "examCode" => $examCode,
            "callback" => env('APP_URL'),
        ];
        Log::info('Quiz24Service getExamForAUser request', ['request' => $req]);

        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "examParticipation", $req);
        $res = $response->json();
        $url = null;
        $message = null;
        Log::info('Quiz24Service getExamForAUser response', ['response' => $res]);
        if (isset($res['result']) && is_string($res['result'])) {
            $url = $res['result'];
        } else {
            $message = $res['message'];
        }
        return compact('url', 'message');
    }

    static function getExamReportForAUser($userName, $examCode)
    {
        $req = [
            "userId" => env('QUIZ24_SCHOOL_ID', 3525433),
            "userName" => $userName,
            "examCode" => $examCode,
        ];
        Log::info('Quiz24Service getExamReportForAUser request', ['request' => $req]);

        $response = Http::withHeaders([
            "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
        ])
            ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "examResult", $req);
        $res = $response->json();
        $url = null;
        $message = null;
        return $res;
        Log::info('Quiz24Service getExamReportForAUser response', ['response' => $res]);
        if (isset($res['result']) && is_string($res['result'])) {
            $url = $res['result'];
        } else {
            $message = $res['message'];
        }
        return compact('url', 'message');
    }


    static function updateStudent(array $userDate)
    {
        try {
            $userDate["userId"] = env('QUIZ24_SCHOOL_ID', 3525433);
            $response = Http::withHeaders([
                "X-API-KEY" => env("QUIZ24_TOKEN", "apikey-f5d5aae0-a0af-41d1-b2bf-1d69fb01cb60")
            ])
                ->post(env("QUIZ24_URL", "https://www.quiz24.ir/api/v1/") . "updateStudent", $userDate);

            $res = $response->json();

            return $res;
        } catch (\Exception $e) {
            Log::error('Quiz24Service updateStudent error', ['error' => $e->getMessage()]);
            return null;
        }
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

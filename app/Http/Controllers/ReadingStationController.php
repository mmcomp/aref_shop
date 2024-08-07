<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateRequest;
use App\Http\Requests\ReadingStationIndexRequest;
use App\Http\Requests\ReadingStationUpdateRequest;
use App\Http\Resources\ReadingStation2Collection;
use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Utils\ReadingStationSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationController extends Controller
{
    public function __construct(
        protected ReadingStationSms $smsProvider,
    ) {
    }

    function store(ReadingStationCreateRequest $request)
    {
        if ($request->table_start_number > $request->table_end_number) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station start table number should be less or equal end table number!']],
            ])->response()->setStatusCode(400);
        }
        ReadingStation::create(["name" => $request->name, "table_start_number" => $request->table_start_number, "table_end_number" => $request->table_end_number]);
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function update(ReadingStationUpdateRequest $request)
    {
        $readingStation = ReadingStation::find($request->id);
        if (!$readingStation) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station not found!']],
            ])->response()->setStatusCode(404);
        }
        if ($request->table_start_number > $request->table_end_number) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station start table number should be less or equal end table number!']],
            ])->response()->setStatusCode(400);
        }
        if ($request->name) {
            if ($request->name !== $readingStation->name) {
                $found = ReadingStation::where("name", $request->name)->first();
                if ($found) {
                    return (new ReadingStationResource(null))->additional([
                        'errors' => ['reading_station' => ['Reading station with the same name exists!']],
                    ])->response()->setStatusCode(400);
                }
            }
            $readingStation->name = $request->name;
        }
        if ($request->table_start_number) {
            $readingStation->table_start_number = $request->table_start_number;
        }
        if ($request->table_end_number) {
            $readingStation->table_end_number = $request->table_end_number;
        }
        $readingStation->save();
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function destroy(ReadingStation $readingStation)
    {
        if (count($readingStation->users) !== 0) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station has users!']],
            ])->response()->setStatusCode(400);
        }
        $readingStation->delete();
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function index(ReadingStationIndexRequest $request)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStations = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        $paginatedReadingStations = ReadingStation::where('id', '>', 0);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId === null) {
                return (new ReadingStation2Collection(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            }
            $paginatedReadingStations->where('id', $readingStationId);
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStation2Collection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function findOne(ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationResource(null))->additional([
                    'errors' => ['reading_station' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        $availableTables = $this->availableTables($readingStation);
        $readingStation->availableTables = $availableTables;
        return (new ReadingStationResource($readingStation))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function testSms()
    {
        return $this->sendSms('09153068145', 'حامد', 'شاکری', '۱۴۰۳/۰۶/۰۱', '۱۴۰۳/۰۶/۰۷', 47, 12, 5, 0);
    }

    public function sendSms($mobile, $firstName, $lastName, $from, $to, $studnetReadingAvarage, $stationReadingAvarage, $absents, $lates)
    {
        $disabled = env('KAVENEGAR_ENABLE') == '0';

        $data = "کل-مطالعه-دانش-آموز:{$studnetReadingAvarage}" . "\n" .
            "میانگین-مطالعه-مرکز:{$stationReadingAvarage}" . "\n" .
            "تعداد-غیبت:{$absents}" . "\n" .
            "تعداد-تأخیر:{$lates}";
        if (!$disabled) {
            $this->smsProvider->send($mobile, [
                "$firstName-$lastName",
                "$from-الی-$to",
                $data,
            ]);
        }

        return collect([
            "mobile" => $mobile,
            "token" => "$firstName-$lastName",
            "token2" => "$from-الی-$to",
            "token3" => $data
        ]);
    }

    function validateMobile($mobile)
    {
        return preg_match('/^09[0-9]{9}+$/', $mobile);
    }

    public function cleanCellPhone($input)
    {
        if (!$this->validateMobile($input)) return null;

        return $input;
    }

    public function minuteToHours($minutes)
    {
        $minute = ($minutes % 60) < 10 ? '0' . ($minutes % 60): ($minutes % 60);
        return intdiv($minutes, 60) . ':' . $minute;
    }

    public function getStudentInfoForSms(ReadingStation $readingStation)
    {
        $result = [];
        $readingStationUsers = ReadingStationUser::where('reading_station_id', $readingStation->id)
            ->with(['user'])->get();

        foreach ($readingStationUsers  as  $readingStationUser) {
            $res = [];
            $lastWeekEnd = Carbon::now()->endOfWeek(Carbon::FRIDAY)->subtract('days', 7)->toDateString();
            $lastWeeklyProgram = ReadingStationWeeklyProgram::where('reading_station_user_id', $readingStationUser->id)
                ->whereDate('end', $lastWeekEnd)->first();
            if (!$lastWeeklyProgram) {
                // return [
                //     'errors' => ['Reading station user last weekly program not found'],
                // ];
                continue;
            }
            $phones = [];
            if ($this->cleanCellPhone($readingStationUser->user->email)) {
                $phones[] = $readingStationUser->user->email;
            }
            if ($this->cleanCellPhone($readingStationUser->user->father_cell) && !in_array($readingStationUser->user->father_cell, $phones)) {
                $phones[] = $readingStationUser->user->father_cell;
            }
            if ($this->cleanCellPhone($readingStationUser->user->mother_cell) && !in_array($readingStationUser->user->mother_cell, $phones)) {
                $phones[] = $readingStationUser->user->mother_cell;
            }
            $firstName = $readingStationUser->user->first_name;
            $lastName = $readingStationUser->user->last_name;
            $from = jdate(strtotime($lastWeeklyProgram->start))->format("Y/m/d");
            $to = jdate(strtotime($lastWeeklyProgram->end))->format("Y/m/d");
            $studnetReadingAvarage = $this->minuteToHours($lastWeeklyProgram->required_time_done + $lastWeeklyProgram->optional_time_done);
            $absents = $lastWeeklyProgram->absent_day;
            $lates = $lastWeeklyProgram->late_day;
            $stationReadingAvarage = $this->minuteToHours(ReadingStationWeeklyProgram::whereHas('readingStationUser', function ($q1) use ($readingStation) {
                $q1->where('reading_station_id', $readingStation->id);
            })
                ->whereDate('end', $lastWeekEnd)
                ->avg('required_time_done') +
                ReadingStationWeeklyProgram::whereHas('readingStationUser', function ($q1) use ($readingStation) {
                    $q1->where('reading_station_id', $readingStation->id);
                })
                ->whereDate('end', $lastWeekEnd)
                ->avg('optional_time_done'));
            // dd($readingStationUser->user->id, $phones, $firstName, $lastName, $from, $to, $studnetReadingAvarage, $stationReadingAvarage, $absents, $lates);
            foreach ($phones as $mobile) {
                $res[] = $this->sendSms($mobile, str_replace(' ', '-', $firstName), str_replace(' ', '-', $lastName), $from, $to, $studnetReadingAvarage, $stationReadingAvarage, $absents, $lates);
            }

            $result[] = $res;
        }

        return $result;
    }

    function encodeURIComponent($str)
    {
        $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')');
        return strtr(rawurlencode($str), $revert);
    }

    private function availableTables(ReadingStation $readingStation): array
    {
        $occupideTables = $readingStation->users->map(function (ReadingStationUser $user) {
            return $user->table_number;
        })->toArray();

        $result = [];
        for ($i = $readingStation->table_start_number; $i <= $readingStation->table_end_number; $i++) {
            if (!in_array($i, $occupideTables)) {
                $result[] = $i;
            }
        }
        return $result;
    }
}

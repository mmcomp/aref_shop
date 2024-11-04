<?php

namespace App\Console\Commands;

use App\Models\ReadingStationPackage;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationUserStrike;
use App\Models\ReadingStationWeeklyProgram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAllWeeklyPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-weekly-programs {--all=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Student All Weekly Points if you pass `--all=true` as option';


    protected $testId = null;
    protected $chunkSize = 100;

    private function getTime(ReadingStationSlutUser $slutUser, $isRequired = true): int
    {
        $is_required = $slutUser->is_required === 1;
        if ($is_required !== $isRequired) return 0;

        $time = $slutUser->slut->duration;
        switch ($slutUser->status) {
            case 'late_15':
                $time -= 15;
                break;
            case 'late_30':
                $time -= 30;
                break;
            case 'late_45':
                $time -= 45;
                break;
            case 'late_60':
                $time -= 60;
                break;
            case 'present':
                $time -= 0;
                break;
            default:
                $time = 0;
        }

        return $time;
    }

    private function addUncreatedWeeklyPrograms()
    {
        $defaultPoint = 2;
        $endOfThisWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $startOfThisWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        $thisWeekPrograms = ReadingStationWeeklyProgram::whereDate('end', $endOfThisWeek)
            ->with(['sluts', 'readingStationUser'])
            ->get();
        foreach ($thisWeekPrograms as $thisWeekProgram) {
            if (count($thisWeekProgram->sluts) === 0 && $thisWeekProgram->noprogram_point === 0) {
                // $thisWeekProgram->point -= $defaultPoint;
                $thisWeekProgram->noprogram_point = $defaultPoint;
                $thisWeekProgram->save();
                // $readingStationUser = $thisWeekProgram->readingStationUser;
                // $readingStationUser->total -= $defaultPoint;
                // $readingStationUser->save();
            }
        }
        $okReadingStationUserIds = ReadingStationWeeklyProgram::whereDate('end', $endOfThisWeek)->pluck('reading_station_user_id');
        $readingStationUsers = ReadingStationUser::where('table_number', '!=', null)->whereNotIn('id', $okReadingStationUserIds)->get();
        $packageIds = $readingStationUsers->pluck('default_package_id');
        $packages = ReadingStationPackage::whereIn('id', $packageIds)->get();
        $query = [];
        foreach ($readingStationUsers as $readingStationUser) {
            $package = $packages->where('id', $readingStationUser->default_package_id)->first();
            $query[] = [
                'reading_station_user_id' => $readingStationUser->id,
                'start' => $startOfThisWeek,
                'end' => $endOfThisWeek,
                'name' => $package->name,
                'required_time' => $package->required_time,
                'optional_time' => $package->optional_time,
                'noprogram_point' => $defaultPoint,
                'point' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $readingStationUser->total -= $defaultPoint;
            $readingStationUser->save();
        }
        ReadingStationWeeklyProgram::insert($query);
    }

    /**
     * Execute the console command.
     */
    public function handle($start = 0)
    {
        echo "Start = $start, ChunkSize = $this->chunkSize\n";
        $arg = $this->option('all');
        $isAll = false;
        if (isset($arg[0]) && $arg[0] === 'true') {
            $isAll = true;
        }
        if ($isAll) {
            if ($start == 0) {
                $users = DB::table('reading_station_users');
                if (isset($this->testId)) {
                    $users->where('id', $this->testId);
                }
                $users->update(['total' => 0]);
                $weeklyProgramUpdates = DB::table('reading_station_weekly_programs');
                if (isset($this->testId)) {
                    $weeklyProgramUpdates->where('reading_station_user_id', $this->testId);
                }
                $weeklyProgramUpdates
                    ->update([
                        'being_point' => 0,
                        'point' => 0,
                        'package_point' => 0,
                        'absent_day' => 0,
                        'approved_absent_day' => 0,
                        'semi_approved_absent_day' => 0,
                        'late_day' => 0,
                        'present_day' => 0,
                    ]);
            }
            $weeklyPrograms = ReadingStationWeeklyProgram::query();
            if (isset($this->testId)) {
                $weeklyPrograms->where('reading_station_user_id', $this->testId);
            }
            $count = $weeklyPrograms->count();
            if ($start >= $count) {
                return;
            }
            $weeklyPrograms = ReadingStationWeeklyProgram::query();
            if (isset($this->testId)) {
                $weeklyPrograms->where('reading_station_user_id', $this->testId);
            }
            $weeklyPrograms = $weeklyPrograms->orderBy('reading_station_user_id')
                ->orderBy('end', 'desc')
                ->skip($start)
                ->take($this->chunkSize)
                ->get();
            $this->clearDuplicates($weeklyPrograms);
            $weeklyPrograms = ReadingStationWeeklyProgram::query();
            if (isset($this->testId)) {
                $weeklyPrograms->where('reading_station_user_id', $this->testId);
            }
            $weeklyPrograms = $weeklyPrograms->orderBy('reading_station_user_id')
                ->orderBy('end', 'desc')
                ->skip($start)
                ->take($this->chunkSize)
                ->get();
        } else {
            $endOfThisWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY)->subtract('days', 7)->toDateString();
            $weeklyPrograms = ReadingStationWeeklyProgram::whereDate('end', $endOfThisWeek)->with('readingStationUser');
            if (isset($this->testId)) {
                $weeklyPrograms->where('reading_station_user_id', $this->testId);
            }
            $weeklyPrograms = $weeklyPrograms->get();
        }
        $this->addUncreatedWeeklyPrograms();


        $this->_handle($weeklyPrograms);

        $this->handle($start + $this->chunkSize);
    }

    private function clearDuplicates($weeklyPrograms)
    {
        foreach ($weeklyPrograms as $weeklyProgram) {
            ReadingStationWeeklyProgram::where('reading_station_user_id', $weeklyProgram->reading_station_user_id)
                ->whereDate('end', $weeklyProgram->end)
                ->where('id', '!=', $weeklyProgram->id)
                ->whereDoesntHave('sluts')
                ->delete();
        }
    }

    public function _handle($weeklyPrograms)
    {
        $absents = 0;
        $availables = 0;
        $beeings = 0;
        $packages = 0;
        $noprograms = 0;
        $strikess = 0;
        $latess = 0;
        foreach ($weeklyPrograms as $weeklyProgram) {
            $score = 0;
            if (!$weeklyProgram->readingStationUser) continue;
            if (count($weeklyProgram->sluts) === 0) continue;
            if (!$weeklyProgram->sluts->where('status', '!=', 'defined')->where('deleted_at', null)->first()) continue;
            $readingStationUser = $weeklyProgram->readingStationUser;
            echo "Week[$readingStationUser->id $weeklyProgram->id] : $weeklyProgram->start - $weeklyProgram->end\n";

            $noprograms += $weeklyProgram->noprogram_point;


            $required_time_done = 0;
            foreach ($weeklyProgram->sluts as $slutUser) {
                $required_time_done += $this->getTime($slutUser);
            }
            $optional_time_done = 0;
            foreach ($weeklyProgram->sluts as $slutUser) {
                $optional_time_done += $this->getTime($slutUser, false);
            }

            $weeklyProgram->required_time_done = $required_time_done;
            $weeklyProgram->optional_time_done = $optional_time_done;
            // echo "required_time_done = $required_time_done\n";
            // echo "optional_time_done = $optional_time_done\n";


            $absent_day = $weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'absent')
                ->count();
            $approved_absent_day = $weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'absent')
                ->where('absense_approved_status', 'approved')
                ->count();
            $semi_approved_absent_day = $weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'absent')
                ->where('absense_approved_status', 'semi_approved')
                ->count();
            $late_day = $weeklyProgram->sluts->where('deleted_at', null)
                // ->where('status', 'like', 'late_%')
                ->where('is_required', true)
                ->filter(function ($slt) {
                    return strpos($slt->status, 'late_') === 0;
                })
                ->count();
            $present_day = $weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'present')
                ->count();
            // echo "absent_day = $absent_day\n";
            // echo "approved_absent_day = $approved_absent_day\n";
            // echo "semi_approved_absent_day = $semi_approved_absent_day\n";
            echo "late_day = $late_day\n";
            // echo "present_day = $present_day\n";
            $weeklyProgram->absent_day = $absent_day;
            $weeklyProgram->approved_absent_day = $approved_absent_day;
            $weeklyProgram->semi_approved_absent_day = $semi_approved_absent_day;
            $weeklyProgram->late_day = $late_day;
            $weeklyProgram->present_day = $present_day;
            $absentScore = -1 * ($weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'absent')
                ->where('absense_approved_status', 'not_approved')
                ->count()) * 2;
            $absentScore += -1 * ($weeklyProgram->sluts->where('deleted_at', null)
                ->where('status', 'absent')
                ->where('absense_approved_status', 'semi_approved')
                ->count());
            $lateScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('status', '!=', 'late_60_plus')->where('is_required', true)->filter(function ($slt) {
                return strpos($slt->status, 'late_') === 0;
            })->count();
            $late60PlusScore = -2 * $weeklyProgram->sluts->where('deleted_at', null)->where('is_required', true)->where('status', 'late_60_plus')->count();
            // echo "absentScore = $absentScore\n";
            $absents += $absentScore;
            echo "lateScore = $lateScore\n";
            $latess += $lateScore;
            $latess += $late60PlusScore;
            echo "late60PlusScore = $late60PlusScore\n";
            $score += $absentScore + $lateScore + $late60PlusScore;

            $slutUsers = $weeklyProgram->sluts->pluck('id');
            $strikes = 0;
            $strikesRecords = ReadingStationUserStrike::whereIn('reading_station_slut_user_id', $slutUsers)->with('readingStationStrike')->get(); //->sum('reading_station_strike_score');
            foreach ($strikesRecords as $strikesRecord) {
                $isPoint = $strikesRecord->readingStationStrike->is_point != 0 ? 1 : -1;
                $strikes += $isPoint * $strikesRecord->reading_station_strike_score;
            }
            $strikess += $strikes;
            $score += $strikes;
            // echo "strikes = $strikes\n";

            if (Carbon::now()->gte($weeklyProgram->end)) {
                // echo "total = $readingStationUser->total score = $score\n";
                $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
                $package = $weeklyProgram->readingStationUser->package;
                $user = $weeklyProgram->readingStationUser->user;
                $scoreChange = 0;
                // package diff done score
                if ($diff < 0) {
                    $scoreChange = -2;
                } elseif ($diff > 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    $step = ($package->step ?? 10) * 60;
                    $scoreChange = (($diff - ($diff % $step)) * 2 / $step);
                }
                $score += $scoreChange;
                // echo "Available : diff = $diff scoreChange = $scoreChange score = $score\n";
                $availables += $scoreChange;

                $weeklyProgram->being_point = 0;
                // no absent score
                if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    $score += 3;
                    // echo "no absent +3 score = $score\n";
                    $weeklyProgram->being_point += 3;
                    $beeings += 3;
                }

                // package grade score
                // echo "beforeGrade score:" . $score . "\n";
                // echo "Checking grade point:" . $package->grade . " !> " . $user->grade . "\n";
                if ($package->grade && $user->grade && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    if ($package->grade > $user->grade) {
                        $score += ($package->grade - $user->grade) * 3;
                        $weeklyProgram->package_point += ($package->grade - $user->grade) * 3;
                        $packages += ($package->grade - $user->grade) * 3;
                        // echo "Grade score:" . $score . "\n";
                    }
                }
            }


            // addUncreatedWeeklyPrograms
            // echo "addUncreatedWeeklyPrograms: -$weeklyProgram->noprogram_point\n";
            $score -= $weeklyProgram->noprogram_point;

            echo "Final score:" . $score . "\n";
            $weeklyProgram->point += $score;
            $weeklyProgram->save();

            $readingStationUser->last_weekly_program = $weeklyProgram->id;
            $readingStationUser->total += $score;
            $readingStationUser->save();
            echo "==============================================================\n";
            $lastWeekProgram = $weeklyProgram;
        }

        echo "absents = $absents\n";
        echo "latess = $latess\n";
        echo "availables = $availables\n";
        echo "beeings = $beeings\n";
        echo "packages = $packages\n";
        echo "noprograms = -$noprograms\n";
        echo "strikess = $strikess\n";
        echo "latess = $latess\n";
    }
}

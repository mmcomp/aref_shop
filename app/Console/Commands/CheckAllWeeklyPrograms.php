<?php

namespace App\Console\Commands;

use App\Models\ReadingStationSlutUser;
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
    protected $signature = 'app:check-all-weekly-programs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Student All Weekly Points';

    private function getTime(ReadingStationSlutUser $slutUser, $isRequired = true): int
    {
        echo "getTime " . $slutUser->id . ", is_required = " . $slutUser->is_required . ", status = " . $slutUser->status . ", time = " . $slutUser->slut->duration . "\n";
        if ($slutUser->is_required !== $isRequired) return 0;

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
        echo "time = $time\n";

        return $time;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('reading_station_users')->update(['total' => 0]);
        DB::table('reading_station_weekly_programs')->update([
            'being_point' => 0,
            'point' => 0,
            'package_point' => 0,
            'absent_day' => 0,
            'approved_absent_day' => 0,
            'semi_approved_absent_day' => 0,
            'late_day' => 0,
            'present_day' => 0,
        ]);
        // $lastEnd = Carbon::now()->endOfWeek(Carbon::FRIDAY)->subtract('days', 7);
        $weeklyPrograms = ReadingStationWeeklyProgram::all(); // ::whereDate('end', '<=', $lastEnd->toDateString())->get();
        foreach ($weeklyPrograms as $weeklyProgram) {
            if (!$weeklyProgram->readingStationUser) continue;
            if (count($weeklyProgram->sluts) === 0) continue;
            if (!$weeklyProgram->sluts->where('status', '!=', 'defined')->where('deleted_at', null)->first()) continue;
            $readingStationUser = $weeklyProgram->readingStationUser;
            if ($readingStationUser->id !== 16) {
                continue;
            }
            echo "Week[$readingStationUser->id $weeklyProgram->id] : $weeklyProgram->start - $weeklyProgram->end\n";
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
            echo "required_time_done = $required_time_done\n";
            echo "optional_time_done = $optional_time_done\n";
            continue;


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
            echo "absent_day = $absent_day\n";
            echo "approved_absent_day = $approved_absent_day\n";
            echo "semi_approved_absent_day = $semi_approved_absent_day\n";
            echo "late_day = $late_day\n";
            echo "present_day = $present_day\n";
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
            $lateScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('is_required', true)->filter(function ($slt) {
                return strpos($slt->status, 'late_') === 0;
            })->count();
            $late60PlusScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('is_required', true)->where('status', 'late_60_plus')->count();
            echo "absentScore = $absentScore\n";
            echo "lateScore = $lateScore\n";
            echo "late60PlusScore = $late60PlusScore\n";
            $score = $absentScore + $lateScore + $late60PlusScore;

            $slutUsers = $weeklyProgram->sluts->pluck('id');
            $strikes = 0;
            $strikesRecords = ReadingStationUserStrike::whereIn('reading_station_slut_user_id', $slutUsers)->with('readingStationStrike')->get(); //->sum('reading_station_strike_score');
            foreach ($strikesRecords as $strikesRecord) {
                $isPoint = $strikesRecord->readingStationStrike->is_point != 0 ? 1 : -1;
                $strikes += $isPoint * $strikesRecord->reading_station_strike_score;
            }
            $score += $strikes;
            echo "strikes = $strikes\n";

            if (Carbon::now()->gte($weeklyProgram->end)) {
                echo "total = $readingStationUser->total score = $score\n";
                $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
                $package = $weeklyProgram->readingStationUser->package;
                $user = $weeklyProgram->readingStationUser->user;
                $scoreChange = 0;
                // package diff done score
                if ($diff < 0) {
                    $scoreChange = -2;
                } elseif ($diff > 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    $step = ($package->step ?? 10) * 60;
                    $scoreChange = (($diff - ($diff % $step)) * 2 / $step) - 2;
                }
                $score += $scoreChange;
                echo "Available : diff = $diff scoreChange = $scoreChange score = $score\n";

                $weeklyProgram->being_point = 0;
                // no absent score
                if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    $score += 3;
                    echo "no absent +3 score = $score\n";
                    $weeklyProgram->being_point += 3;
                }

                // package grade score
                echo "beforeGrade score:" . $score . "\n";
                echo "Checking grade point:" . $package->grade . " !> " . $user->grade . "\n";
                if ($package->grade && $user->grade && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    if ($package->grade > $user->grade) {
                        $score += ($package->grade - $user->grade) * 3;
                        $weeklyProgram->package_point += ($package->grade - $user->grade) * 3;
                        echo "Grade score:" . $score . "\n";
                    }
                }
            }

            echo "Final score:" . $score . "\n";
            $weeklyProgram->point += $score;
            $weeklyProgram->save();

            $readingStationUser->last_weekly_program = $weeklyProgram->id;
            $readingStationUser->total += $score;
            $readingStationUser->save();
            echo "==============================================================\n";
        }
    }
}

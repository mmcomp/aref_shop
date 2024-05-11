<?php

namespace App\Console\Commands;

use App\Models\ReadingStationUserStrike;
use App\Models\ReadingStationWeeklyProgram;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckWeeklyPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-weekly-programs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Student Weekly Points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endOfThisWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $weeklyPrograms = ReadingStationWeeklyProgram::whereDate('end', $endOfThisWeek)->with('readingStationUser')->get();
        foreach ($weeklyPrograms as $weeklyProgram) {
            if (!$weeklyProgram->readingStationUser) continue;
            if (count($weeklyProgram->sluts) === 0) continue;
            if (!$weeklyProgram->sluts->where('status', '!=', 'defined')->where('deleted_at', null)->first()) continue;
            $readingStationUser = $weeklyProgram->readingStationUser;
            // if ($readingStationUser->id !== 11) {
            //     continue;
            // }

            echo "Week[$readingStationUser->id] : $weeklyProgram->start - $weeklyProgram->end\n";
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
            $late60PlusScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('status', 'late_60_plus')->count();
            echo "absentScore = $absentScore\n";
            echo "lateScore = $lateScore\n";
            echo "late60PlusScore = $late60PlusScore\n";
            $score = $absentScore + $lateScore + $late60PlusScore;

            $slutUsers = $weeklyProgram->sluts->pluck('id');
            $strikes = ReadingStationUserStrike::whereIn('reading_station_slut_user_id', $slutUsers)->sum('reading_station_strike_score');
            $score -= $strikes;
            echo "strikes = $strikes\n";

            echo "total = $readingStationUser->total score = $score\n";
            $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
            $package = $weeklyProgram->readingStationUser->package;
            $user = $weeklyProgram->readingStationUser->user;
            if (Carbon::now()->gte($weeklyProgram->end)) {
                // package diff done score
                if ($diff < 0) {
                    $score += -2;
                } elseif ($diff > 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                    $step = ($package->step ?? 10) * 60;
                    $score += (($diff - ($diff % $step)) * 2 / $step) - 2;
                }
            }
            echo "diff = $diff score = $score\n";

            $weeklyProgram->being_point = 0;
            // no absent score
            if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0 && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                $score += 3;
                echo "no absent +3 score = $score\n";
                $weeklyProgram->being_point += 3;
            }

            // package grade score
            echo "beforeGrade score:" . $score . "\n";
            echo "Checking grade point:" . $package->grade . " !> ". $user->grade . "\n";
            if ($package->grade && $user->grade && $weeklyProgram->required_time_done >= $weeklyProgram->required_time) {
                if ($package->grade > $user->grade) {
                    $score += ($package->grade - $user->grade) * 3;
                    $weeklyProgram->package_point += ($package->grade - $user->grade) * 3;
                    echo "Grade score:" . $score . "\n";
                }
            }

            $weeklyProgram->point += $score;
            $weeklyProgram->save();

            $readingStationUser->last_weekly_program = $weeklyProgram->id;
            $readingStationUser->total += $score;
            $readingStationUser->save();
        }
    }
}

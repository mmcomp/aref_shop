<?php

namespace App\Console\Commands;

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('reading_station_users')->update(['total'=>0]);
        $weeklyPrograms = ReadingStationWeeklyProgram::all();
        foreach ($weeklyPrograms as $weeklyProgram) {
            if (!$weeklyProgram->readingStationUser) continue;
            if (count($weeklyProgram->sluts) === 0) continue;
            if ($weeklyProgram->sluts->where('status', 'defined')->where('deleted_at', null)->first()) continue;
            $absentScore = -1 * ($weeklyProgram->sluts->where('deleted_at', null)->where('status', 'absent')->count()) * 2;
            $lateScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('status', 'like', 'late_%')->count();
            $late60PlusScore = -1 * $weeklyProgram->sluts->where('deleted_at', null)->where('status', 'late_60_plus')->count();
            echo "absentScore = $absentScore\n";
            echo "lateScore = $lateScore\n";
            echo "late60PlusScore = $late60PlusScore\n";
            $readingStationUser = $weeklyProgram->readingStationUser;
            if ($readingStationUser->id !== 11) {
                continue;
            }
            $score = $absentScore + $lateScore + $late60PlusScore;
            echo "total = $readingStationUser->total score = $score\n";
            $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
            $package = $weeklyProgram->readingStationUser->package;
            $user = $weeklyProgram->readingStationUser->user;
            if ($readingStationUser->last_weekly_program !== $weeklyProgram->id) {
                // package diff done score
                if ($diff < 0) {
                    $score = -2;
                } elseif ($diff > 0) {
                    $step = ($package->step ?? 10) * 60;
                    $score = ($diff - ($diff % $step)) * 2 / $step;
                }
            }
            echo "diff = $diff score = $score\n";


            // no absent score
            if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0) {
                $score += 3;
                echo "no absent +3 score = $score\n";
                $weeklyProgram->being_point += 3;
            }

            // package grade score
            if ($package->grade && $user->grade) {
                if ($package->grade > $user->grade) {
                    $score += ($package->grade - $user->grade) * 3;
                    $weeklyProgram->being_point += ($package->grade - $user->grade) * 3;
                    echo "package grade " . (($package->grade - $user->grade) * 3) . " score = $score\n";
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

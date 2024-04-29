<?php

namespace App\Console\Commands;

use App\Models\ReadingStationWeeklyProgram;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $weeklyPrograms = ReadingStationWeeklyProgram::all();
        foreach ($weeklyPrograms as $weeklyProgram) {
            if (!$weeklyProgram->readingStationUser) continue;
            $readingStationUser = $weeklyProgram->readingStationUser;
            if ($readingStationUser->id !== 11) {
                continue;
            }
            $score = 0;
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
                $weeklyProgram->being_point += 3;
            }
            echo "no absent score = $score\n";

            // package grade score
            if ($package->grade && $user->grade) {
                if ($package->grade > $user->grade) {
                    $score += ($package->grade - $user->grade) * 3;
                    $weeklyProgram->being_point += ($package->grade - $user->grade) * 3;
                }
            }
            echo "package grade score = $score\n";

            $weeklyProgram->point += $score;
            $weeklyProgram->save();

            $readingStationUser->last_weekly_program = $weeklyProgram->id;
            $readingStationUser->total += $score;
            $readingStationUser->save();
        }
    }
}

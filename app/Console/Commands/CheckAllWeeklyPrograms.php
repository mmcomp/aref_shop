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
            $score = 0;
            $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
            if (!$weeklyProgram->readingStationUser) continue;
            $package = $weeklyProgram->readingStationUser->package;
            $user = $weeklyProgram->readingStationUser->user;
            $readingStationUser = $weeklyProgram->readingStationUser;
            if ($readingStationUser->last_weekly_program === $weeklyProgram->id) {
                continue;
            }

            // package diff done score
            if ($diff < 0) {
                $score = -2;
            } elseif ($diff > 0) {
                $step = ($package->step ?? 10) * 60;
                $score = ($diff - ($diff % $step)) * 2 / $step;
            }

            // no absent score
            if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0) {
                $score += 3;
                $weeklyProgram->being_point += 3;
            }

            // package grade score
            if ($package->grade && $user->grade) {
                if ($package->grade > $user->grade) {
                    $score += ($package->grade - $user->grade) * 3;
                    $weeklyProgram->being_point += ($package->grade - $user->grade) * 3;
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

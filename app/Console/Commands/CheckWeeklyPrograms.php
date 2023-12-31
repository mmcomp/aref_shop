<?php

namespace App\Console\Commands;

use App\Models\ReadingStationPackage;
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startOfThisWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        $weeklyPrograms = ReadingStationWeeklyProgram::whereDate('updated_at', '>=', $startOfThisWeek)->get();
        foreach ($weeklyPrograms as $weeklyProgram) {
            $score = 0;
            $diff = $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done - $weeklyProgram->required_time - $weeklyProgram->optional_time;
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
                $step = $package->step ?? 10;
                $score = ($diff - ($diff % $step)) * 2 / $step;
            }

            // no absent score
            if ($weeklyProgram->absent_day === 0 && $weeklyProgram->late_day === 0) {
                $score += 3;
            }

            // package grade score
            if ($package->grade && $user->grade) {
                if ($package->grade > $user->grade) {
                    $score += ($package->grade - $user->grade) * 3;
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

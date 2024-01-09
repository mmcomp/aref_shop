<?php

namespace App\Utils;

use App\Models\ReadingStationAbsentPresent;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SlutUserValidation
{
    private $day;
    private $userSluts;
    private $absentPresent;
    private $lastAbsentPresent;
    private $lastExitSlut;
    function __construct(private ReadingStationWeeklyProgram $weeklyProgram, private ReadingStationSlut $slut, private User $user, private string $status, string $day = null)
    {
        $this->day = $day ?? Carbon::now()->toDateString();
        $this->userSluts = ReadingStationSlutUser::where('reading_station_weekly_program_id', $this->weeklyProgram->id)
            ->where('day', $this->day)
            ->where('is_required', 1)
            ->withAggregate('slut', 'start')
            ->orderBy('slut_start')
            ->get();
        $this->absentPresent = ReadingStationAbsentPresent::where('reading_station_id', $slut->reading_station_id)
            ->where('user_id', $user->id)
            ->where('day', $this->day)
            ->where('is_processed', 0)
            ->first();
        $this->lastAbsentPresent = ReadingStationAbsentPresent::where('reading_station_id', $slut->reading_station_id)
            ->where('user_id', $user->id)
            ->where('day', $this->day)
            ->where('is_processed', 1)
            ->orderBy('updated_at', 'desc')
            ->first();
        if ($this->lastAbsentPresent && $this->lastAbsentPresent->reading_station_slut_user_exit_id) {
            $this->lastExitSlut = $this->lastAbsentPresent->slutUserExit;
        }
    }

    public function start(): void
    {
        switch ($this->status) {
            case 'absent':
                if (!$this->isTheFirstSlut()) {
                    throw new HttpException(400, 'You should exit the user first!');
                }
                break;
            case 'late_15':
            case 'late_30':
            case 'late_45':
            case 'late_60':
            case 'late_60_plus':
                if (!$this->isTheFirstSlut()) {
                    throw new HttpException(400, 'You should exit the user first!');
                }
                break;

            default:
                throw new HttpException(400, 'You should exit the user first!');
                break;
        }
    }

    public function isTheFirstSlut(): bool
    {
        $firstSlutUser = $this->userSluts[0];
        if ($this->lastExitSlut) {
            $firstSlutUser = $this->userSluts;
            $firstSlutUser = $firstSlutUser->where('slut_start', '>', $this->lastExitSlut->start)->first();
        }
        return $firstSlutUser->reading_station_slut_id === $this->slut->id;
    }
}

<?php

namespace App\Http\Controllers\Utils;

use App\Models\ReadingStation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReadingStationAuth {
    public static function checkUserWithReadingStationAuth(ReadingStation $readingStation, User $user = null): bool
    {
        switch (Auth::user()->group->type) {
            case 'admin_reading_station_branch':
                $readingStationId = Auth::user()->reading_station_id;
                if ($readingStationId !== $readingStation->id) {
                    return false;
                }
                if ($user && $user->readingStationUser && $user->readingStationUser->reading_station_id !== $readingStation->id) {
                    return false;
                }
                break;

            case 'user':
                if ($user->readingStationUser->reading_station_id !== $readingStation->id) {
                    return false;
                }
        }
        return true;
    }
}

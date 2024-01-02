<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationEducationalReportRequest;
use App\Models\ReadingStation;
use Illuminate\Http\Request;

class ReadingStationReportController extends Controller
{
    public function educational(ReadingStationEducationalReportRequest $request, ReadingStation $readingStation)
    {
        return [];
    }
}

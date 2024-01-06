<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    public $id;
    function __construct($id)
    {
        $this->id = $id;
    }

    public function map($user): array
    {
        return [
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->national_code,
            $user->readingStationUser->table_number,
            $user->school,
            $user->major,
            $user->grade,
        ];
    }

    public function query()
    {
        $id = $this->id;
        return User::whereHas('readingStationUser', function ($q1) use ($id) {
            $q1->where('reading_station_id', $id);
        });
    }

    public function headings(): array
    {
        return [
            'نام',
            'نام خانوادگی',
            'تلفن همراه',
            'کد ملی',
            'شماره میز',
            'مدرسه',
            'رشته',
            'مقطع',
        ];
    }
}

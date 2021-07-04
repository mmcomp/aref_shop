<?php

namespace App\Utils;

class TheDate
{

    public function getSaturdayAndFriday($date)
    {
        $myDate = $date;
        $today_dayofweek = strtolower(date("l", strtotime($myDate)));
        $saturday = $today_dayofweek != 'saturday' ? date('Y-m-d', strtotime("last saturday", strtotime($myDate))) : date('Y-m-d', strtotime("saturday", strtotime($myDate)));
        $friday = date('Y-m-d', strtotime("next friday", strtotime($saturday)));
        return ['saturday' => $saturday, 'friday' => $friday];
    }
}

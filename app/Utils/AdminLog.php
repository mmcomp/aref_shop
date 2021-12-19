<?php

namespace App\Utils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Audit;
use App\Models\User;

 class AdminLog
 {
     public static function addLog($user_id,$before,$after)//($user_id,$user_fullName,$before,$after)
     {
        $user=User::whereId($user_id)->first();
        $user_fullName=$user->first_name . " " . $user->last_name;
        $log_result=self::createLog($user->id,$user_fullName,$before,$after);
        return $log_result;
     }
     public function createLog($user_id,$user_fullName,$before,$after)//($user_id,$before,$after)
     {
        $data=[
            "user_id" => $user_id,
            "user_name" => $user_fullName,
            "before" => $before,
            "after" => $after
        ];
        $logCreated=Audit::create($data);
        return $logCreated;
        // $audit=new AdminLog;
        // $user=User::whereId($user_id)->first();
        // // dd($user->first_name);
        // $user_fullName=$user->first_name . " " . $user->last_name;
        // //dd($user_fullName);
        // $log_result=AdminLog::deleteRecord($user->id,$user_fullName,$before,$after);
        //  return $log_result;
 
     }
 }





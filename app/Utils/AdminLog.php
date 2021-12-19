<?php

namespace App\Utils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Audit;

 class AdminLog
 {
     public static function deleteRecord($user_id,$user_fullName,$before,$after)
     {
         $data=[
             "user_id" => $user_id,
             "user_name" => $user_fullName,
             "before" => $before,
             "after" => $after
         ];

       $logCreated=Audit::create($data);
       return $logCreated;
     }
 }





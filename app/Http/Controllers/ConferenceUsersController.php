<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConferenceUser;
use App\Http\Requests\ConferenceUsersGetOneRequest;
use DB;
use App\Models\user;
use App\Models\ProductDetailVideo;
//use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\Paginator;
use App\Http\Resources\ConferenceUsersShowResource;

class ConferenceUsersController extends Controller
{
    public function show($product_detail_videos_id)
    { 
        $result=DB::table("conference_users")
        ->leftJoin('users','users.id','=','users_id')
        ->select('email','first_name','last_name' , 'referrer' , 'product_detail_videos_id')    
        ->where('product_detail_videos_id',"$product_detail_videos_id")
        ->groupBy('email')
        ->get();   
       
       //$result=new Paginator($conference, 50);
       //return response()->json($conference,200);
       return  ConferenceUsersShowResource::collection($result);
    }
}

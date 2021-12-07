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
use App\Http\Resources\ConferenceUsersResource;

class ConferenceUsersController extends Controller
{
    public function showReport($product_detail_videos_id)
    { 
        $result=DB::table("conference_users")
        ->leftJoin('users','users.id','=','users_id')
        ->select('email','first_name','last_name' , 'referrer' , 'product_detail_videos_id')    
        ->where('product_detail_videos_id',"$product_detail_videos_id")
        ->groupBy('email')
        //->paginate(2);
        ->get();   
       
       //$result=new Paginator($conference, 50);
       //return response()->json($conference,200);
       return  ConferenceUsersShowResource::collection($result);
    }
    public function index()
    { 
        //dd("ghgh");
//         SELECT distinct `conference_users`.`product_detail_videos_id`,`product_detail_videos`.`name` FROM `conference_users`
// left join `product_detail_videos` on (`product_detail_videos`.id=`conference_users`.`product_detail_videos_id`)


        $result=DB::table("conference_users")
        ->leftJoin('product_detail_videos','product_detail_videos.id','=','conference_users.product_detail_videos_id')
        ->select('product_detail_videos_id','name')    
        //->where('product_detail_videos_id',$product_detail_videos_id")
        ->distinct()
        //->groupBy('email')
        //->paginate(2);
        ->get();   
       
       //$result=new Paginator($conference, 50);
       //return response()->json($conference,200);
       return  ConferenceUsersResource::collection($result);
    }   
}

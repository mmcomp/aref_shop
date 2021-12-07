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
       $conference=DB::select("SELECT  email,first_name,last_name , referrer , product_detail_videos_id , name  FROM conference_users left join users on (users.id = users_id) 
       left join product_detail_videos on (product_detail_videos.id=product_detail_videos_id)
       WHERE product_detail_videos.id=418 group by users.email");
       
       //$result=new Paginator($conference, 50);
       //return response()->json($conference,200);
       return  ConferenceUsersShowResource::collection($conference);
    }
}

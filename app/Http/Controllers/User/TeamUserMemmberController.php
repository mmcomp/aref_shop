<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserMemmberCreateRequest;
use App\Http\Requests\User\TeamUserMemmberEditRequest;
use App\Http\Resources\User\TeamUserMemmberResource;
use App\Http\Resources\User\TeamUserResource;
use App\Http\Resources\User\TeamUserMemmberErrorResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;



use App\Models\TeamUserMemmber;
use App\Models\TeamUser;
use App\Models\User;

class TeamUserMemmberController extends Controller
{ 
    public function index()
    {
        $data= TeamUserMemmberResource::collection(TeamUserMemmber::all());
        return $data;
    }

    public function store(TeamUserMemmberCreateRequest $teamUserMemmber)
    {
       // $count= TeamUserMemmber::where("team_user_id",$teamUserMemmber->team_user_id)->count();
        //dd($count);
        //dd("dfd");
        $teamUserMemmber["is_verified"]=false;
        $leaderexist=User::where("email",$teamUserMemmber["mobile"])->first();
        if( $leaderexist !==null)
        {
            $this->errorHandle("User","this mobile is a leadder so you can not add to your memmber");
        }
        $data=TeamUserMemmber::create($teamUserMemmber->toArray());
        // dd($data['id']);
        // if($data!==null)
        // {

        // }
        return new TeamUserMemmberResource($data);
    }
    public function update(TeamUserMemmberEditRequest $teamUserMemmber,$team_user_memmber_id)
    {
        $teamUserMemmberobj=TeamUserMemmber::where("id",$team_user_memmber_id)->first();
        
        if($teamUserMemmberobj !==null && $teamUserMemmberobj["is_verified"]===0 && $teamUserMemmber["is_verified"]===1)
        {
            $team_user_id=$teamUserMemmberobj["team_user_id"];
            $this->updateTeamUserMemmber($teamUserMemmberobj);
            if(self::isCountToEnd($team_user_id))
            {
               $this->updateTeamUser($team_user_id);
            }
            return response()->json($teamUserMemmber,200);            
        }
        else
        { 
            //dd("sdf");           
           $this->errorHandle("TeamUserMemmberResource","TeamUserMemmber not found or it is verified before");
          // dd("gggg");
            // return (new TeamUserMemmberResource(null))->additional([
            //     'errors' => ['TeamUserMemmberResource' => ['TeamUserMemmber not found or it is verified before ']],
            // ])->response()->setStatusCode(404);
        }
    }
    public function destroyMemmber()
    {
        
    }
    public  function updateTeamUserMemmber(TeamUserMemmber $teamUserMemmber)
    {
        $teamUserMemmber["is_verified"]=true;
        $updatetd=$teamUserMemmber->update();
        if(!$updatetd)
        {
            $this->errorHandle("TeamUserMemmberResource","fail to update");
            // return (new TeamUserMemmberResource(null))->additional([
            //     'errors' => ['TeamUserMemmberUpdate' => ['fail to update']],
            // ])->response()->setStatusCode(404);           
        }
    }
    public static function isCountToEnd($team_user_id)
    {
        $teamUserCount=TeamUserMemmber::where("team_user_id",$team_user_id)->where("is_verified",1)->count();
        if($teamUserCount >=2)
               return true;
        return false; 
    }
    public function updateTeamUser($team_user_id)
    {       
        $teamUser= TeamUser::find($team_user_id);
        //dd( $teamUser);
        if($teamUser!==null)
        {
            $teamUser["is_full"]=1;
            //$teamUser=$teamUser->update();
            if(!$teamUser->update())
            {
              $this->errorHandle("TeamUser","fail to update");
                // return (new TeamUserMemmberResource(null))->additional([
                //     'errors' => ['TeamUserUpdate' => ['fail to update']],
                // ])->response()->setStatusCode(404);
            }
        }
        else
        {
            $this->errorHandle("TeamUser","not found");
            // return (new TeamUserResource(null))->additional([
            //     "errors"=>["teamuser" => ["not found"]],
            // ])->response()->setStatusCode(404);
        }
        
    }
    public function errorHandle($class,$error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],
               
            ],422)
        );
    }

}

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
use Illuminate\Support\Facades\Auth;


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
        $data="";
        $user=User::where('id',Auth::user()->id)->with("teamUser")->first();      
        $teamUserMemmber["is_verified"]=false;
        $teamUserMemmber["team_user_id"]=$user->teamUser->id;
        //$leaderexist=User::where("email",$teamUserMemmber["mobile"])->first();
        if($user)
        {
           if($this->avoidDuplicate($user->teamUser->id,$teamUserMemmber["mobile"]))
           {
            $data=TeamUserMemmber::create($teamUserMemmber->toArray());
           }
           else{
            $this->errorHandle("User","this mobile has alreade been added");
           }           
        }       
        return new TeamUserMemmberResource($data);
    }
    public function update(TeamUserMemmberEditRequest $request,int $teamUserId)
    {   //dd($request->mobile);
        $user=User::where('id',Auth::user()->id)->with("teamUser")->first();
        // dd($user);
        // dd($teamUserId);
         $teamUserMemmberobj=TeamUserMemmber::where("mobile",$request->mobile)->where("team_user_id",$teamUserId)->first();
        //dd( $teamUserMemmberobj->toArray());
        if($user && $teamUserId)
        {
                        //$team_user_id=$teamUserMemmberobj["team_user_id"];           
            if(!$this->isCountToEnd($teamUserId))
            {
                $this->updateTeamUserMemmber($teamUserMemmberobj);               
            }
            else{
                $this->updateTeamUser($teamUserId);
            }            
            return response()->json($teamUserMemmberobj,200);            
        }
        else
        { 
            //dd("sdf");           
           $this->errorHandle("TeamUserMemmberResource","TeamUserMemmber not found or it is verified before");         
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
        }
    }
    protected  function isCountToEnd(int $team_user_id)
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
            }
            else
            {
                $teamUserMemmber=TeamUserMemmber::where("team_user_id",$team_user_id)->where("is_verified",0)->get();
                $teamUserMemmber->delete();
               // dd($teamUserMemmber);
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
    protected function avoidDuplicate(int $teamUserId,string $mobile)
    {
       if( TeamUserMemmber::where("team_user_id",$teamUserId)->where("mobile",$mobile)->first())
        return false;
       return true;
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Http\Requests\ShowFilteredTeamUserRequest;

use App\Models\TeamUser;
use App\Models\TeamUserMemmber;
use App\Models\User;

class ShowAllTeamUserController extends Controller
{
    public function index()
    {  
       $allTeams= $this->getAllTeams();
        return (new  ShowAllTeamResource($allTeams))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    public function filter(ShowFilteredTeamUserRequest $request)
    { 
        $member=TeamUserMemmber::where("mobile",$request->mobile)->with("teamUser.leader")->with("teamUser.TeamMember.member")->first();
        $leader=User::where("email",$request->mobile)->with("teamUser.member")->first();
        //dd($member);
        if($member)
        {
            return($member);
        }
        if($leader)
        {
            return($leader);
        }
        // $leader= TeamUser::with("leader")->whereHas("leader",function($query) use ($request){           
        //     $query->where("email",$request->mobile);
        // })->get();  
        // if($leader)   
        // {
        //     ///find member
        // }
        // {
        //     //find member 
        //     //ifmember exist then find leader
        // }     
        // dd($leader);
    }
    protected function getAllTeams()
    {
        // $team=
        // [   
        //     "teams" => [],     
        // ]; 
        $team=
        [
            "name" =>"",
            "is_full"=>'',
            "creator" => "",
            "members"=>[]
        ];     
        $teams=null;
    $allTeams=TeamUser::with("TeamMember.member")->orderBy('id',"Asc")->paginate(env('PAGE_COUNT')); 
         
        if(count($allTeams)>0)
        {
            $id=0;
            foreach($allTeams as $allTeam)
            {
               // dd($allTeam["TeamMember"]);
                $team["name"]=$allTeam["name"];
                $team["is_full"]=$allTeam["is_full"];
                $team["creator"]=$allTeam["user_id_creator"];  
                  
                if($allTeam["TeamMember"] !==null)
                {
                    $team["members"]=$this->getMembers($allTeam["user_id_creator"],$allTeam["TeamMember"]);                         
                }                        
                $teams["teams"][]=$team;                
            }                    
        } 
        return $teams ;     
        //dd($allTeam);
    }
    protected function getMembers(int $userId,$teamMembers)
    {
        $members=null;
        // $members=[            
        //     "mobile"=>"",
        //     "name"=>"",
        //     "isCreator" => "" ,
        //     "isVerified" =>"",
        // ];
        $count=0;
        //dd($teamMembers);
        foreach($teamMembers as $teamMember)
        {
            //dump($teamMember);            
            if($teamMember["member"] !==null)
            {
                $members["mobile"]=$teamMember["member"]["email"];
                $members["name"]=$teamMember["member"]["first_name"]." ".$teamMember["member"]["last_name"];
                $members["isVerified"]=$teamMember->is_verified;
                $members["isCreator"]=0;               
            }
            else
            {
                $members["mobile"]=null;
                $members["name"]=null;
                $members["isVerified"]=null;
                $members["isCreator"]=0;               
            }         
            $team[$count]=$members;         
            $count++;
        }        
        $team[$count]=$this->getLeader($userId);//$members;
        return  $team;  
    }
    protected function getLeader(int  $userId)
    {
        if($userId)
             $user=User::find($userId);
        /////////////////// for leader //////////////////////
            if($user)
            {
                $members["mobile"]=$user->email;
                $members["name"]=$user->first_name ."-". $user->last_name;
                $members["isVerified"]=null;
                $members["isCreator"]=1;
            }
            else
            {
                $members["mobile"]=null;
                $members["name"]=null;
                $members["isVerified"]=null;
                $members["isCreator"]=1; 
            }
            return $members;
    }
}

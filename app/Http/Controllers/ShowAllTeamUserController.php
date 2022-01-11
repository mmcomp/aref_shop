<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Http\Requests\ShowFilteredTeamUserRequest;
use App\Http\Resources\ShowFilteredTeamResource;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        $team=
        [
            "name" =>"",
            "is_full"=>"",
            "creator" => "",
            "members"=>[]
        ];     
         $user=User::where("email",$request->mobile)->with("teamUser.TeamMember.member")->first(); 
         if($user)
         {
             //it is only  user that registered
             if($user->teamUser)// a user has a team so it is leader
             {               
                $team["name"]=$user->teamUser->name;           
                $team["is_full"]=$user->teamUser->is_full;          
                $team["creator"]=$user->teamUser->user_id_creator;  
                $team["members"]=$this->getMembers($user["id"],$user->teamUser->TeamMember); 
             }
             else//it isn't  a leader user just registerd   
             {               
                $member=TeamUserMemmber::where("mobile",$request->mobile)->with("teamUser.leader")->with("teamUser.TeamMember.member")->first();
               if($member!==null)  //because i dont have leader in $member->teamUser->TeamMember and finally i get leader seperatelly and put in the last member insdex
               {
                $team["name"]= $member->teamUser->name;           
                $team["is_full"]= $member->teamUser->is_full;          
                $team["creator"]= $member->teamUser->user_id_creator;  
                $team["members"]=$this->getMembers( $member["id"], $member->teamUser->TeamMember); 
                $team["members"][count($team["members"])-1]=$this->getLeader($member->teamUser->user_id_creator);
               }
               else{
                    $this->errorHandle("User", "this user doesn't have any team.");
               }
             }             
         }
         else ///this user is not registered yet 
         {
            $this->errorHandle("User", "this user is not registered yet ");
         }        
        return $team ;    
        
    }
    protected function getAllTeams()
    {       
        $team=
        [
            "id" =>"",
            "name" =>"",
            "is_full"=>'',
            "creator" => "",
            "created_at" => "",
            "members"=>[]
        ];     
        $teams=null;
        $allTeams=TeamUser::with("TeamMember.member")->orderBy('id',"Asc")->paginate(env('PAGE_COUNT'));          
        if(count($allTeams)>0)
        {
            $id=0;
            foreach($allTeams as $allTeam)
            { 
                $team["id"]=$allTeam["id"];             
                $team["name"]=$allTeam["name"];
                $team["is_full"]=$allTeam["is_full"];
                $team["creator"]=$allTeam["user_id_creator"];  
                $team["created_at"]=date('Y-m-d H:i:s', strtotime($allTeam["created_at"]));  
                  
                if($allTeam["TeamMember"] !==null)
                {
                    $team["members"]=$this->getMembers($allTeam["user_id_creator"],$allTeam["TeamMember"]);                         
                }                        
                $teams["teams"][]=$team;                
            }                    
        } 
        return $teams ;   
    }
    protected function getMembers(int $userId,$teamMembers)
    {       
        $members=null;      
        $count=0;       
        foreach($teamMembers as $teamMember)
        {                   
            if($teamMember["member"] !==null)
            {
                $members["mobile"]=$teamMember["member"]["email"];
                $members["name"]=$teamMember["member"]["first_name"]." ".$teamMember["member"]["last_name"];
                $members["isVerified"]=$teamMember->is_verified;
                $members["isCreator"]=0;               
            }
            else
            {
                $members["mobile"]=$teamMember["mobile"];
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
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }
}

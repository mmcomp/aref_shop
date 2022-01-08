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
            "is_full"=>'',
            "creator" => "",
            "members"=>[]
        ];     
         $user=User::where("email",$request->mobile)->with("teamUser.TeamMember.member")->first();  
         //dd($user);       
         if($user)
         {
             //it is only  user that registered
           
                
             if($user->teamUser)// a user has a team so it is leader
             {

               dd($user->teamUser->TeamMember->toArray());
                $team["name"]=$user["name"];           
                $team["creator"]=$user["id"];  
                $team["members"]=$this->getMembers($user["id"],$user->teamUser->TeamMember->toArray()); 
             }
             else//it isn't  a leader user just registerd   
             {
                $member=TeamUserMemmber::where("mobile",$request->mobile)->with("teamUser.leader")->with("teamUser.TeamMember.member")->first();
               // dd($member);
             }
             
         }
         else ///this user is not registered yet 
         {
            $this->errorHandle("User", "this user is not registered yet ");
         }
        
        // $team["leader"]=$leader;
        
        // //dd($member);
        // $team["member"]=$member;
        //return (new ShowFilteredTeamResource($team));
      
        // $teams=null;                
        // if(count($leader)>0)
        // {
            
        //     if($leader["TeamMember"] !==null)
        //     {
        //         $team["members"]=$this->getMembers($allTeam["user_id_creator"],$allTeam["TeamMember"]);                         
        //     }                        
        //     $teams["teams"][]=$team;                
                               
        // } 
        return $team ;    
        
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
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }
}

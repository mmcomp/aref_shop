<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Models\TeamUser;
use App\Models\User;

class ShowAllTeamUserController extends Controller
{
    public function index()
    {  
       $allTeams= $this->getAllTeams();
        return (new ShowAllTeamResource($allTeams));
    }
    protected function getAllTeams()
    {
        $team=
        [   
            "teams" => [],     
        ]; 
        $team=
        [
            "name" =>"",
            "is_full"=>'',
            "creator" => "",
            "members"=>[]
        ];
        $members=[
            //"id"=>"",
            "mobile"=>"",
            "name"=>"",
            "isCreator" => ""
            //"is_verified"=>"",
        ];

        $allTeams=TeamUser::with("TeamMember.member")->get();       
        if(count($allTeams)>0)
        {
            $id=0;
            foreach($allTeams as $allTeam)
            {
               // dd($allTeam["TeamMember"]);
                $team["name"]=$allTeam["name"];
                $team["is_full"]=$allTeam["is_full"];
                $team["creator"]=$allTeam["user_id_creator"];
                if($allTeam["user_id_creator"] !==null)
                   $user=User::find($allTeam["user_id_creator"]);
                  
                if($allTeam["TeamMember"] !==null)
                {
                      $mem=$this->getMembers($allTeam["TeamMember"]);                         
                }                        
                $teams["teams"][]=$team;
                //$id++;
            }
            //dd("ebd dump");          
        }       
        //dd($allTeam);
    }
    protected function getMembers($teamMembers)
    {
        $count=0;
        foreach($teamMembers as $teamMember)
        {            
            if($teamMember["member"] !==null)
            {
                $members["mobile"]=$teamMember["member"]["email"];
                $members["name"]=$teamMember["member"]["first_name"]." ".$teamMember["member"]["last_name"];
                $members["isCreator"]=0;
                // dump($members["mobile"] . "- " .$members["name"] );
            }
            else
            {
                $members["mobile"]=null;
                $members["name"]=null;
                $members["isCreator"]=0;
                // dump($members["mobile"] . "- " .$members["name"] );
            }
            // dump($members["mobile"] . "- " .$members["name"] );                      
            // dump($members);
            // $members["is_verified"]=$teamMember["member"]["is_verified"];
            $team["members"][$count]=$members;
            //dd($teamMember["member"]["first_name"]);
            //$members=null;
            $count++;
        }
        /////////////////// for leader //////////////////////
            if($user)
            {
                $members["mobile"]=$user->email;
                $members["name"]=$user->first_name ."-". $user->last_name;
                $members["isCreator"]=1;
            }
            else
            {
                $members["mobile"]=null;
                $members["name"]=null;
                $members["isCreator"]=1; 
            }
            $team["members"][$count]=$this->getLeader()$members;   
    }
    protected function getLeader()
    {

    }
}

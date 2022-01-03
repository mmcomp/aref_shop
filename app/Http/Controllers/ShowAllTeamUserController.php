<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Models\TeamUser;

class ShowAllTeamUserController extends Controller
{
    public function index()
    {  
        $teams=
        [   
            "team" => [],     
        ]; 
        $team=
        [
            "name" =>"",
            "is_full"=>'',
            "creator" => "",
            "members"=>[]
        ];
        $members=[
            "id"=>"",
            "mobile"=>"",
            "name"=>"",
            "is_verified"=>"",
        ];

        $allTeams=TeamUser::with("TeamMember.member")->get();
        //dd($allTeams);
        //$allTeams=TeamUser::all()->toArray();
        //dd($allTeams);
        //dd($allTeams[0]["team_member"][0]["member"]["first_name"]);
        //dd($allTeams->team_member->member->first_name);
        if(count($allTeams)>0)
        {
            $id=0;
            foreach($allTeams as $allTeam)
            {
                //dd($allTeam);
                $team["name"]=$allTeam["name"];
                $team["is_full"]=$allTeam["is_full"];
                $team["creator"]=$allTeam["user_id_creator"];
                $teams["team"][]=$team;
                $id++;
            }
            dd($teams);
        }
       
        //dd($allTeam);
        return (ShowAllTeamResource::collection($team));

    }
}

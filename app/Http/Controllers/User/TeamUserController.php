<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserCreateRequest;
use App\Http\Requests\User\TeamUserEditRequest;
use App\Http\Resources\User\TeamUserResource;
use App\Http\Resources\User\TeamUserMemmberResource;
use App\Http\Resources\User\TeamUserWithMemmberResource;
use App\Http\Resources\User\TeamUserWithoutMemmberResource;

use App\Models\TeamUser;
use App\Models\User;
use App\Models\TeamUserMemmber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class TeamUserController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        $ownTeam = $this->getMyTeamAsLeader($userId);     
        if ($ownTeam === null) {
            $userMemmbers = TeamUserMemmber::where("mobile", Auth::user()->email)->with('member')->get();
           
            if (count($userMemmbers) >0 && $userMemmbers[0]["is_verified"] == 1) { 
                return $this->getMyTeamAsMember(Auth::user()->email);
            } else {
                return $this->showLeadersAsGhost(Auth::user()->email);
            }
        }
        //return (new TeamUserWithMemmberResource($ownTeam));
        return $ownTeam;
        // throw new HttpResponseException(
        //     response()->json([
        //         'errors' => ["error" => ["this is users memmber"]],

        //     ],422)
        // );
    }

    protected function getMyTeamAsLeader(int $userId)
    {
        $userTeam = TeamUser::where("user_id_creator", $userId)->with('leader')->first();       
        if ($userTeam === null) {            
            return null;
        }

        $userMemmbers = TeamUserMemmber::where("team_user_id", $userTeam->id)->with('member')->get();
        $userTeam["memmbers"] = $userMemmbers;
       
        return (new TeamUserWithMemmberResource($userTeam));
        // return [
        //     "userTeam" => $userTeam,
        //     "userMemmbers" => $userMemmbers,
        // ];
    }


    protected function getMyTeamAsMember(string $mobile)
    {       
        $teamUsermembers = TeamUserMemmber::where("mobile", $mobile)->with('teamUser')->where("is_verified", 1)->get()->toArray();
       
        if (count($teamUsermembers) === 0) {
          
            return (new TeamUserWithoutMemmberResource(null)); 
        }
        if (count($teamUsermembers) <= 1) {
                               
            return $this->getMyTeamAsLeader($teamUsermembers[0]["team_user"]["user_id_creator"]);
        } else {
            $teams = [
                "id"    => $teamUsermembers->team_user_id,
                "teams" => [],
            ];
            foreach ($teamUsermembers as $teammember) {
                $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
                $team = [
                    "teamName" => $teammember["team_user"]["name"],
                    "leaderFullName" => $userTeam->leader->first_name . " " . $userTeam->leader->last_name,
                ];
                $teams["teams"][] = $team;
            }

            return (new TeamUserWithoutMemmberResource($teams));
        }
    }
    protected function showLeadersAsGhost(string $mobile)
    {       
        $teamUsermembers = TeamUserMemmber::where("mobile", $mobile)->where("is_verified", 0)->with('teamUser')->get()->toArray();
      
        if (count($teamUsermembers) === 0) {           
            return (new TeamUserWithoutMemmberResource(null));
        } else { ///////////////////////////////////////////////////               just userrrrrr is not member
           // $id = 0;
            $teams = [];
            foreach ($teamUsermembers as $teammember) {
                $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
                $team = [
                    "teamName" => $teammember["team_user"]["name"],
                    "leaderFullName" => $userTeam->leader->first_name . " " . $userTeam->leader->last_name,
                    "id" => $userTeam->id,
                ];
                $teams[] = $team;
            }
            // foreach ($teamUsermembers as $teammember) {
            //     $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
            //     $teams[$id]["teamName"] = $teammember["team_user"]["name"];
            //     $teams[$id]["leaderFullName"] = $userTeam->leader->first_name . " " . $userTeam->leader->last_name;
            //     $id++;
            // }           
            return (new TeamUserWithoutMemmberResource($teams));
            // return[
            //     "teams" => $teams               
            // ] ;         
        }
    }

    public function _index()
    {
        $user_id = Auth::user()->id;
        $user_name = Auth::user()->first_name . " " . Auth::user()->last_name;       
        $userTeam = TeamUser::where("user_id_creator", $user_id)->first();
        if ($userTeam) {
            $team_tmp["team_name"] = $userTeam->name;
            $team_tmp["creator_name"] = $user_name;
            $team_tmp["memmbers"] = array();
            if ($userTeam->id !== null) {
                $userMemmbers = TeamUserMemmber::where("team_user_id", $userTeam["id"])->pluck("mobile")->toArray();
               
                if (isset($userMemmbers)) {
                    $id = 0;
                    // $memmber["name"]="";
                    // $memmber["mobile"]="";
                    foreach ($userMemmbers as $memmber) {

                        $user_name2 = User::where("email", $memmber)->select("first_name", "last_name")->get()->toArray();
                        $memmber_tmp["name"] = $user_name2[0]["first_name"] . " " . $user_name2[0]["last_name"];
                        $team_tmp["memmbers"][$id]["name"] = $memmber_tmp["name"];
                        $team_tmp["memmbers"][$id]["mobile"] = $memmber;
                       
                        $id++;
                    }                   
                    $team_tmp["memmbers"] = $userMemmbers;
                }              
            }
        } else {
            $team_tmp["team_name"] = "";
            $team_tmp["creator_name"] = "";
            $team_tmp["memmbers"] = [];
        }       

        $tmp = new TeamUserWithMemmberResource($team_tmp);       
        return $tmp;        
        $data = TeamUserResource::collection($userTeam);
        //$data=TeamUserResource::
        return ($data);
        // $data=Fault::all();
        // return response()->json($data,200);
    }
    public function show($id)
    {
        $data = TeamUser::find($id)->first();
        return response()->json($data, 200);
        // $data=Fault::find($id);
        // return response()->json($data,201);
    }
    public function store(TeamUserCreateRequest $request)
    {
        $user_id = Auth::user()->id;
        $request["is_full"] = false;
        $request["user_id_creator"] = $user_id;       
        //$data=Fault::Create($request->all());
        $data = TeamUser::create($request->all());
        return response()->json($data, 200);
    }
    public function update(TeamUserCreateRequest $request, TeamUser $teamUser)
    {
        $data = TeamUser::find($teamUser);
        if ($data !== null) {
        }
        return response()->json($data, 200);
        // return response()->json($id,209);
        //  $validation=self::Validation($id);    
    }
}

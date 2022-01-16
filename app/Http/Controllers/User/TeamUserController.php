<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserCreateRequest;
use App\Http\Requests\User\TeamUserEditRequest;
use App\Http\Resources\User\TeamUserResource;
use App\Http\Resources\User\TeamUserMemberResource;
use App\Http\Resources\User\TeamUserWithMemberResource;
use App\Http\Resources\User\TeamUserWithoutMemberResource;

use App\Models\TeamUser;
use App\Models\User;
use App\Models\TeamUserMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class TeamUserController extends Controller
{
    public function index()
    {        
        $userId = Auth::user()->id;       
        $ownTeam = $this->getMyTeamAsLeader($userId);         
        if ($ownTeam === null) {
            $userMembers = TeamUserMember::where("mobile", Auth::user()->email)->with('member')->get();         
            if (count($userMembers) >0 && $userMembers[0]["is_verified"] == 1) { 
                return(new TeamUserWithMemberResource($this->getMyTeamAsMember(Auth::user()->email)));
            } else { 
                return (new TeamUserWithMemberResource($this->showLeadersAsGuest(Auth::user()->email)));
            }
        }
        return (new TeamUserWithMemberResource($ownTeam));
        //return $ownTeam;
        // throw new HttpResponseException(
        //     response()->json([
        //         'errors' => ["error" => ["this is users Member"]],

        //     ],422)
        // );
    }
    protected function getMyTeamAsLeader(int $userId)
    {       
        $userTeam = TeamUser::where("user_id_creator", $userId)->with("TeamMember.member")->with("leader")->first();  
        return ($userTeam);     
    //     if ($userTeam === null) {            
    //         return null;
    //     }

    //     $userMembers = TeamUserMember::where("team_user_id", $userTeam->id)->with('member')->get();
    //     $userTeam["Members"] = $userMembers;
    //    return $userTeam;       
    }
    protected function getMyTeamAsMember(string $mobile)
    {   
        // $getAllTeamAsMember=TeamUser::where("id",$teamUsermembers[0]["team_user_id"])->with("TeamMember.member")->get();
        //     return (new TeamUserWithoutMemberResource($getAllTeamAsGuest));
        $teamUsermembers = TeamUserMember::where("mobile", $mobile)->with('member.teamUser')->where("is_verified", 1)->get()->toArray();
        $teamUsermembers=TeamUser::where("id",$teamUsermembers[0]["team_user_id"])->with("TeamMember.member")->get();
        return ($teamUsermembers);
        // if (count($teamUsermembers) === 0) {
        //   return null;
        //     return (new TeamUserWithoutMemberResource(null)); 
        // }
        // if (count($teamUsermembers) <= 1) { 

        //     $getLeaderDetails=$this->getMyTeamAsLeader($teamUsermembers[0]["team_user"]["user_id_creator"]);           
        //     return $getLeaderDetails;
        // } else {
        //     $teams = [
        //         "id"    => $teamUsermembers->team_user_id,
        //         "teams" => [],
        //     ];
        //     foreach ($teamUsermembers as $teammember) {
        //         $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
        //         $team = [
        //             "teamName" => $teammember["team_user"]["name"],
        //             "leaderFullName" => $userTeam->leader->first_name . " " . $userTeam->leader->last_name,
        //         ];
        //         $teams["teams"][] = $team;
        //     }

        //     return (new TeamUserWithoutMemberResource($teams));
        // }
    }
    protected function showLeadersAsGuest(string $mobile)
    {       
        $teamUsermembers = TeamUserMember::where("mobile", $mobile)->where("is_verified", 0)->with('teamUser')->get()->toArray();
       // dd($teamUsermembers);
        if (count($teamUsermembers) === 0) {           
            return (new TeamUserWithoutMemberResource(null));
        } else { ///////////////////////////////////////////////////               just userrrrrr is not member
       
            $getAllTeamAsGuest=TeamUser::where("id",$teamUsermembers[0]["team_user_id"])->with("TeamMember.member")->get();
            return (new TeamUserWithoutMemberResource($getAllTeamAsGuest));
            //dd($getAllTeamAsGuest);
            //    // $id = 0;
            //     $teams = [];
            //     foreach ($teamUsermembers as $teammember) {
            //         $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
            //         $team = [
            //             "teamName" => $teammember["team_user"]["name"],
            //             "leaderFullName" => $userTeam->leader->first_name . " " . $userTeam->leader->last_name,
            //             "id" => $userTeam->id,
            //         ];
            //         $teams[] = $team;
            //     }
            // foreach ($teamUsermembers as $teammember) {
            //     $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
            //     $teams[$id]["teamName"] = $teammember["team_user"]["name"];
            //     $teams[$id]["leaderFullName"] = $userTeam->leader->first_name . " " . $userTeam->leader->last_name;
            //     $id++;
            // }           
            
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
            $team_tmp["Members"] = array();
            if ($userTeam->id !== null) {
                $userMembers = TeamUserMember::where("team_user_id", $userTeam["id"])->pluck("mobile")->toArray();
               
                if (isset($userMembers)) {
                    $id = 0;
                    // $Member["name"]="";
                    // $Member["mobile"]="";
                    foreach ($userMembers as $Member) {

                        $user_name2 = User::where("email", $Member)->select("first_name", "last_name")->get()->toArray();
                        $Member_tmp["name"] = $user_name2[0]["first_name"] . " " . $user_name2[0]["last_name"];
                        $team_tmp["Members"][$id]["name"] = $Member_tmp["name"];
                        $team_tmp["Members"][$id]["mobile"] = $Member;
                       
                        $id++;
                    }                   
                    $team_tmp["Members"] = $userMembers;
                }              
            }
        } else {
            $team_tmp["team_name"] = "";
            $team_tmp["creator_name"] = "";
            $team_tmp["Members"] = [];
        }       

        $tmp = new TeamUserWithMemberResource($team_tmp);       
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
       if($this->userCanAddTeam($user_id))
       {
        $this->errorHandle("TeamUser", "fail to add there is one ");
       }     
        //$data=Fault::Create($request->all());
        $data = TeamUser::create($request->all());
        return response()->json($data, 200);
    }
   protected function  userCanAddTeam(int $user_id)
   {
       return (TeamUser::where("user_id_creator",$user_id)->first());
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
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }
}

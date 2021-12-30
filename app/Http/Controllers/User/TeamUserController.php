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
            return $this->getMyTeamAsMember(Auth::user()->email);
        }
        return $ownTeam;
        // throw new HttpResponseException(
        //     response()->json([
        //         'errors' => ["error" => ["this is users memmber"]],
                
        //     ],422)
        // );
    }

    protected function getMyTeamAsLeader(int $userId)  {
        $userTeam = TeamUser::where("user_id_creator", $userId)->with('leader')->first();
        //dd($userTeam->toArray());
       //return(new TeamUserWithMemmberResource($userTeam));
        if ($userTeam === null) {
            return null;
        }

        $userMemmbers = TeamUserMemmber::where("team_user_id", $userTeam->id)->with('member')->get();
        $userTeam["memmbers"]=$userMemmbers;
        //dd($userTeam);
        return (new TeamUserWithMemmberResource($userTeam));
        // return [
        //     "userTeam" => $userTeam,
        //     "userMemmbers" => $userMemmbers,
        // ];
    }


    protected function getMyTeamAsMember(string $mobile) {
        //dd("just user memmber is run");
        $teamUsermembers = TeamUserMemmber::where("mobile", $mobile)->with('teamUser')->get()->toArray();
       
        if(count($teamUsermembers)===0)
        {
           // dd( $teamUsermembers );
            return (new TeamUserWithoutMemmberResource(null));
        }
       
        //dd($teamUsermembers);
       //dd($teamUsermembers[0]["team_user"]["user_id_creator"]);
       //dd($teamUsermembers[0]["team_user"]);
        if(count($teamUsermembers)<=1 )
        {  

            return $this->getMyTeamAsLeader($teamUsermembers[0]["team_user"]["user_id_creator"]);
        }
        else{
            $id=0;
            foreach($teamUsermembers as $teammember )
            {
                $userTeam = TeamUser::where("user_id_creator", $teammember["team_user"]["user_id_creator"])->with('leader')->first();
                $teams[$id]["teamName"]=$teammember["team_user"]["name"];
                $teams[$id]["leaderFullName"]=$userTeam->leader->first_name . " " . $userTeam->leader->last_name  ;
                $id++;
            }
            //dd($teamUsermembers);
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
        //dd( $user_name);
        $userTeam = TeamUser::where("user_id_creator", $user_id)->first();
        if ($userTeam) {
            $team_tmp["team_name"] = $userTeam->name;
            $team_tmp["creator_name"] = $user_name;
            $team_tmp["memmbers"] = array();
            if ($userTeam->id !== null) {
                $userMemmbers = TeamUserMemmber::where("team_user_id", $userTeam["id"])->pluck("mobile")->toArray();
                //dd($userMemmber);
                if (isset($userMemmbers)) {
                    $id = 0;
                    // $memmber["name"]="";
                    // $memmber["mobile"]="";
                    foreach ($userMemmbers as $memmber) {

                        $user_name2 = User::where("email", $memmber)->select("first_name", "last_name")->get()->toArray();
                        $memmber_tmp["name"] = $user_name2[0]["first_name"] . " " . $user_name2[0]["last_name"];
                        //dd($memmber_tmp["name"]);

                        $team_tmp["memmbers"][$id]["name"] = $memmber_tmp["name"];
                        $team_tmp["memmbers"][$id]["mobile"] = $memmber;
                        //dd($team_tmp);
                        $id++;
                    }
                    // dd($team_tmp);
                    // User::where("email",)
                    $team_tmp["memmbers"] = $userMemmbers;
                }

                // dd($userMemmber->toArray());
            }
        } else {
            $team_tmp["team_name"] = "";
            $team_tmp["creator_name"] = "";
            $team_tmp["memmbers"] = [];
        }


        //dd($userTeam->id);

        $tmp = new TeamUserWithMemmberResource($team_tmp);
        //    dd($tmp);
        return $tmp;
        // dd($userTeam->toArray());
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
        //dd();      
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

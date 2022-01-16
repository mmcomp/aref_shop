<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Http\Requests\ShowFilteredTeamUserRequest;
use App\Http\Requests\ReplaceTeamMemberRequest;
use App\Http\Resources\ShowAllTeamCollection;
use App\Http\Resources\User\TeamUserMemberResource;
use App\Http\Resources\ShowFilteredTeamResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\TeamUser;
use App\Models\TeamUserMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Utils\Sms;

class ShowAllTeamUserController extends Controller
{
    private $mobile;
    public function __construct(OrderController $orderController)
    {
        $this->mobile=new Sms;
    }
    public function addTeamMember(ReplaceTeamMemberRequest $teamMember)
    { 
       $team= TeamUser::where("id",$teamMember->team_user_id)->with("leader")->with("leader.teamUser.TeamMember")->first();
      // dd($team->leader->last_name);
       // $user = User::where('id', $teamMember->team_user_id)->with("teamUser")->first();    
        $adminUser=$this->isAdmin(); 
        
        if(!$adminUser)    
        {
            $this->errorHandle("Admin", "برای ویرایش شماره موبایل حتما باید دسترسی سطح مدیریتی داشته باشید.");
        }        
        if(!$this->validTeam($teamMember->team_user_id)) 
        {
            $this->errorHandle("teamValid", "تیم وارد شده معتبر نمی باشد.");
        } 
        // if(!$this->validMember($teamMember->team_user_id,$teamMember->mobile))  
        // {
        //     $this->errorHandle("teamMember", "شماره موبایل  ".$teamMember->mobile."   عضو این تیم نیست.");
        // }
      
        $isLeader=$this->isLeader($teamMember->mobile);                   
        if($isLeader)
        { 
            $this->errorHandle("TeamUser", " شماره ".$teamMember->mobile." قبلا به عنوان عضو درج شده است.");
        } 
        if($team->leader->teamuser!==null)
        {
            $exist=TeamUserMember::where("mobile",$teamMember->mobile)->where("is_verified",1)->first();       
            if($exist)
            {                    
                $this->errorHandle("TeamUser", " شماره ".$teamMember->mobile."  قبلا به عنوان عضو درج شده است.");
            }
            $data = ""; 
            //$userFullNmae=str_replace(' ',"-",$team->first_name ."-". $team->last_name);
            $teamUserMember["is_verified"] = false; 
        
            if ($team && $team->leader->teamuser!==null) {
                $teamUserMember["team_user_id"] = $team->leader->teamUser->id;
                $teamUserMember["mobile"] = $teamMember->mobile;

                if ($this->avoidDuplicate($team->leader->teamUser->id, $teamMember->mobile)) {                
                    $data = TeamUserMember::create($teamUserMember);
                    $this->notifyToNotApprovedMembers($team->leader);
                    //$mobile=$teamMember->mobile;               
                    // $this->mobile->sendCode("$mobile",   $userFullNmae, 'verify-team-member');
                } else {
                    // $this->deleteTeam($user->teamUser->id);
                    $this->errorHandle("User", "شماره ".$teamUserMember['mobile']." تکراری است");
                }
            }
        } 
        else
        {
            $this->errorHandle("TeamUser", "لطفا ابتدا تیم را ایجاد کنید.");
        }
        return new TeamUserMemberResource($data);  
    }
    protected function deleteTeamMember(int $teamUserMemberId)
    {
        TeamUserMember::where("id", $teamUserMemberId)->delete();
        return [];
    }  
    public function index()
    {  
       $allTeams= $this->getAllTeams();
        return (new  ShowAllTeamCollection($allTeams))->additional([
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
                $member=TeamUserMember::where("mobile",$request->mobile)->with("teamUser.leader")->with("teamUser.TeamMember.member")->first();
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
        return TeamUser::with("TeamMember.member")->with("leader")->orderBy('created_at',"desc")->paginate(env('PAGE_COUNT', 15));
        // $allTeams=TeamUser::with("TeamMember.member")->orderBy('created_at',"desc")->paginate(env('PAGE_COUNT', 15));          
        // if(count($allTeams)>0)
        // {
        //     $id=0;
        //     foreach($allTeams as $allTeam)
        //     { 
        //         $team["id"]=$allTeam["id"];             
        //         $team["name"]=$allTeam["name"];
        //         $team["is_full"]=$allTeam["is_full"];
        //         $team["creator"]=$allTeam["user_id_creator"];  
        //         $team["created_at"]=date('Y-m-d H:i:s', strtotime($allTeam["created_at"]));  
                  
        //         if($allTeam["TeamMember"] !==null)
        //         {
        //             $team["members"]=$this->getMembers($allTeam["user_id_creator"],$allTeam["TeamMember"]);                         
        //         }                        
        //         $teams["teams"][]=$team;                
        //     }                    
        // } 
        // return $teams ;   
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
                $members["name"]=$user->first_name ." ". $user->last_name;
                $members["isVerified"]=1;
                $members["isCreator"]=1;
            }
            else
            {
                $members["mobile"]=null;
                $members["name"]=null;
                $members["isVerified"]=1;
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
    public function isLeader(string $mobile)
    { 
       $user=User::where("email",$mobile)->first();
       
       if($user!==null)      
       {           
            $response=TeamUser::where("user_id_creator",$user->id)->with("leader")->first();            
            if($response)
            {
                    return true;
            }
            return false;
       }
       else
       {            
            return false;
       }
      
       //dd($response);
       
    }
    public function validTeam(int $teamUserId)
    {
        $team=TeamUser::where("id",$teamUserId)->where("is_full",false)->with("TeamMember")->first();
        return $team;
    }
    public function validMember(int $teamUserId,string $teamUserMember)
    {
        $validMember=TeamUserMember::where("team_user_id",$teamUserId)->where("mobile",$teamUserMember)->with("member")->first();
        return $validMember;
    }
    public function isAdmin()
    {
       $isAdmin= User::where('id', Auth::user()->id)->where("groups_id",1)->with("teamUser")->first(); 
       return $isAdmin;
    }
    protected function avoidDuplicate(int $teamUserId, string $mobile)
    {
        if (TeamUserMember::where("team_user_id", $teamUserId)->where("mobile", $mobile)->first())
            return false;
        return true;
    }
    protected function notifyToNotApprovedMembers($leader)
    {
       //$leader=TeamUser::where() 
      $teamUserId= $leader->teamUser->id;
      $allNotApprovedMembers=TeamUserMember::where("team_user_id",$teamUserId)
      ->with("member")      
      ->where("is_verified",0)
      ->get();
      //dd($allNotApprovedMembers);
      if(count($allNotApprovedMembers)>=2)
      {
        // $userFullName="-";
        //   if(isset($leader))
        //   {
            $userFullName=str_replace(' ',"-",$leader->first_name ."-". $leader->last_name);
        //   }
        //dd($userFullName);
        foreach($allNotApprovedMembers as $allNotApprovedMember)
        {
            //var_dump($allNotApprovedMember->mobile. " " . $userFullName . " " );
           $this->mobile->sendCode($allNotApprovedMember->mobile,$userFullName, 'verify-team-member');
        }
        return true;
      }
      else{
        //$this->errorHandle("TeamUserMember", "fail to update");
        return false;
      }
      
    }
}

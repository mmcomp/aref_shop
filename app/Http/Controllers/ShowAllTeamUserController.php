<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ShowAllTeamResource;
use App\Http\Requests\ShowFilteredTeamUserRequest;
use App\Http\Requests\ReplaceTeamMemberRequest;
use App\Http\Requests\FilterTeamUserRequest;
use App\Http\Resources\ShowAllTeamCollection;
use App\Http\Resources\User\TeamUserMemberResource;
use App\Http\Resources\ShowFilteredTeamResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\TeamUser;
use App\Models\TeamUserMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Utils\Sms;
use Illuminate\Support\Facades\DB;

class ShowAllTeamUserController extends Controller
{
    private $mobile;
    //"there is a comment"
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
    protected function deleteTeam(int $teamUserId)
    {
      if($this->canDeleteTeam($teamUserId))
      {         
           $this->deleteTeamMembers($teamUserId);
           $this->deleteNotVerifiedTeam($teamUserId);
           return response()->json(["successfull"=>true],201);
      }
      else
      {
        $this->errorHandle("TeamUser", "نمی توانید تیم را حذف کنید به دلیل اینکه دارای اعضای فعال می باشد.");
      }           
    } 
    
    protected function deleteTeamMember(int $teamUserMemberId)
    {
        TeamUserMember::where("id", $teamUserMemberId)->delete();
        return [];
    }  
    public function index(FilterTeamUserRequest $request)
    {         
       $allTeams= $this->getAllTeams($request->mobile);
       return (new  ShowAllTeamCollection($allTeams))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }    
    protected function getAllTeams(string $mobile=null)
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
        // $query=DB::table("team_users")
        // //->distinct()
        // ->select(["team_users.id AS team_user_id"])
        // ->leftjoin("users","users.id","=","team_users.user_id_creator")
        // ->where("users.email" ,"like" ,"%$mobile%" )
        // ->get()->toArray();
        $query=TeamUser::with(["leader" => function($q) use($mobile){
            $q->where("email","like" ,"%$mobile%");
        }])->whereHas('leader', function($q) use($mobile) {
            $q->where('email',"like" ,"%$mobile%");
        })->select("id AS team_user_id")
        ->get()->toArray();
        // ->select("id AS team_user_id")
        // ->distinct()
        // ->get()
        // ->toArray();
        //dd($query);
       $query2=TeamUserMember::where("mobile" ,"like" ,"%$mobile%")
       ->select("team_user_id AS team_user_id")
       ->distinct()
       ->get()
       ->toArray();
        $res=array_merge($query2,$query);
        //dd($res);
        $res=collect($res)->map(function ($item, $key) {
            return $item["team_user_id"];
        });
       //dd($res);
        // SELECT team_users.id FROM `team_users`
        // left join users on (users.id=user_id_creator)
        // where users.email like '%09155193106%';

       // SELECT team_user_id  FROM `team_user_members` WHERE `mobile` LIKE '%09155193106%' ;
        //  $query=DB::table('team_users')
        //  ->select("users.email","users.first_name","users.last_name","team_users.id","team_users.created_at","team_users.user_id_creator","team_users.name","team_user_members.mobile","team_user_members.is_verified","team_user_members.team_user_id")
        //  ->leftjoin("users","users.id","=","team_users.user_id_creator")        
        //  ->leftjoin("team_user_members","team_user_members.team_user_id","=","team_users.id")         
        //  ->Where("users.email","$mobile")
        //  //->orWhere("mobile","$mobile")
        //  //->get();
        //  ->orderBy('created_at',"desc")->paginate(env('PAGE_COUNT', 15));
         //dd($query);
        // ->with("TeamMember.member")        
        // ->get();
        // dd($query);
        //$allteams= TeamUser::with("TeamMember.member")->with("leader")->orderBy('created_at',"desc")->paginate(env('PAGE_COUNT', 15));
        $allteams= TeamUser::whereIn("id",$res)
        ->with("TeamMember.member")
        ->with("leader")
        ->orderBy('created_at',"desc")
        ->paginate(env('PAGE_COUNT', 15));
        return($allteams);       
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
      $teamUserId= $leader->teamUser->id;
      $allNotApprovedMembers=TeamUserMember::where("team_user_id",$teamUserId)
      ->with("member")      
      ->where("is_verified",0)
      ->get();
      //dd($allNotApprovedMembers);
      if(count($allNotApprovedMembers)>=2)
      {        
            $userFullName=str_replace(' ',"-",$leader->first_name ."-". $leader->last_name);
        
            foreach($allNotApprovedMembers as $allNotApprovedMember)
            {                
                 $this->mobile->sendCode($allNotApprovedMember->mobile,$userFullName, 'verify-team-member');
            }
            return true;
      }
      else{
        //$this->errorHandle("TeamUserMember", "fail to update");
        return false;
      }
      
    }
    protected function canDeleteTeam(int $teamUserId)
    {     
      if(!$this->checkTeamMemberIsNotVerified($teamUserId))
      {
         // return false;
         $this->errorHandle("TeamUser", "نمی توانید تیم را حذف کنید به دلیل اینکه دارای اعضای فعال می باشد.");
      }
      return true;
      
    }
    protected function checkTeamMemberIsNotVerified(int $teamUserId)
    {     
       $cadDeleteTeam=true; 
       $teamMembers=TeamUserMember::where("team_user_id",$teamUserId)->where("is_verified",1)->get();
       if(count($teamMembers)>0)
       {
          $cadDeleteTeam=false;
       }
       return $cadDeleteTeam;
    }
    protected function deleteTeamMembers(int $teamUserId)
    { 
       $teamMembers=TeamUserMember::where("team_user_id",$teamUserId)->delete();
       return $teamMembers;
    }
    protected function deleteNotVerifiedTeam(int $teamUserId)
    { 
       $team=TeamUser::where("id",$teamUserId)->delete();
       return $team;
    }
}

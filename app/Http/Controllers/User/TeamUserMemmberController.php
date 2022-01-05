<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserMemmberCreateRequest;
use App\Http\Requests\User\TeamUserMemmberEditRequest;
use App\Http\Resources\User\TeamUserMemmberResource;
use App\Http\Resources\User\TeamUserResource;
use App\Http\Resources\User\TeamUserMemmberErrorResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Requests\InsertOrderForUserRequest;

use App\Models\TeamUserMemmber;
use App\Models\TeamUser;
use App\Models\TeamUserProduct;
use App\Models\UserProduct;
use App\Models\TeamProductDefaults;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Utils\Buying;
use App\Utils\Sms;


class TeamUserMemmberController extends Controller
{    
    private $privateOrderController;
    private $smsObj;
    public function __construct(OrderController $orderController)
    {
        $this->smsObj=new Sms;
        $this->privateOrderController = $orderController;
    }
    public function index()
    {
        $data = TeamUserMemmberResource::collection(TeamUserMemmber::all());
        return $data;
    }

    public function store(TeamUserMemmberCreateRequest $teamUserMemmber)
    {
       
        $user = User::where('id', Auth::user()->id)->with("teamUser")->first();      
        $isLeader=$this->isLeader($teamUserMemmber["mobile"]); 
        //dd($isLeader);            
        if($isLeader)
        {            
           if($user->teamUser)
           {
                $this->deleteTeam($user->teamUser->id);
           }             
           $this->errorHandle("Leader", " شماره ".$teamUserMemmber["mobile"]."برای سرگروه می باشد.");
        } 
        if($user->teamuser!==null)
        {
            $exist=TeamUserMemmber::where("mobile",$teamUserMemmber["mobile"])->where("is_verified",1)->first();       
            if($exist)
            {
                $this->deleteTeam($user->teamUser->id);
                $this->errorHandle("TeamUser", " شماره ".$teamUserMemmber["mobile"]." قبلا به عنوان عضو درج شده");
            }
            $data = ""; 
            $userFullNmae=str_replace(' ',"-",$user->first_name ."-". $user->last_name);
            $teamUserMemmber["is_verified"] = false; 
        
            if ($user && $user->teamuser!==null) {
                $teamUserMemmber["team_user_id"] = $user->teamUser->id;
                if ($this->avoidDuplicate($user->teamUser->id, $teamUserMemmber["mobile"])) {                
                    $data = TeamUserMemmber::create($teamUserMemmber->toArray());
                    $mobile=$teamUserMemmber["mobile"];               
                    $this->smsObj->sendCode("$mobile",   $userFullNmae, 'verify-team-member');
                } else {
                    $this->deleteTeam($user->teamUser->id);
                    $this->errorHandle("User", "شماره ".$teamUserMemmber['mobile']." تکراری است");
                }
            }
        } 
        else
        {
            $this->errorHandle("TeamUser", "لطفا ابتدا تیم را ایجاد کنید.");
        }
        return new TeamUserMemmberResource($data);       
    }
    public function update(int $teamUserId)
    {       
        $teamUserMemmberobj = TeamUserMemmber::where("mobile", Auth::user()->email)->where("team_user_id", $teamUserId)->first();
        
        if ($teamUserMemmberobj) {
            //$team_user_id=$teamUserMemmberobj["team_user_id"];           
            $this->updateTeamUserMemmber($teamUserMemmberobj);
            if ($this->isCountToEnd($teamUserId)) {
                $this->updateTeamUser($teamUserId);
            }
            // else{
            //    
            //     $this->updateTeamUser($teamUserId);
            // }   
            return response()->json($teamUserMemmberobj, 200);
        } else {
                    
            $this->errorHandle("TeamUserMemmberResource", "TeamUserMemmber not found or it is verified before");
        }
    }
    public function destroyMemmber()
    {
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
    public  function updateTeamUserMemmber(TeamUserMemmber $teamUserMemmber)
    {
        $teamUserMemmber["is_verified"] = true;       
        $updatetd = $teamUserMemmber->update();
        if (!$updatetd) {
            $this->errorHandle("TeamUserMemmberResource", "fail to update");
        }
        /////////////////////////////////           delete all of teams if this person in invited when the first invited avccept
        $this->delteMySelfFromAllTeam($teamUserMemmber);
    }
    protected function delteMySelfFromAllTeam(TeamUserMemmber $teamUserMemmber)
    {
        $teamUserMemmbers = TeamUserMemmber::where("mobile", $teamUserMemmber->mobile)->where("is_verified", 0)->get();
        foreach ($teamUserMemmbers as $memmber) {
            $memmber->delete();
        }
    }   
    protected  function isCountToEnd(int $team_user_id)
    {
        $teamUserCount = TeamUserMemmber::where("team_user_id", $team_user_id)->where("is_verified", 1)->count();
        if ($teamUserCount >= 2)
            return true;
        return false; 
    }
    public function updateTeamUser($team_user_id)
    {
        $teamUser = TeamUser::find($team_user_id);
        
        if ($teamUser !== null) {
            $teamUser["is_full"] = 1;          
            if (!$teamUser->update()) {               
                $this->errorHandle("TeamUser", "fail to update");
            } else { ///////////////////////////////////  delete all invitation for all people for this group if it is full /////////
               
                $this->buyProductsForTeams($teamUser->id, $teamUser->user_id_creator);
                $teamUserMemmbers = TeamUserMemmber::where("team_user_id", $team_user_id)->where("is_verified", 0)->get();
                foreach ($teamUserMemmbers as $memmber) {
                    $memmber->delete();
                }               
            }
        } else {
            $this->errorHandle("TeamUser", "not found");
            // return (new TeamUserResource(null))->additional([
            //     "errors"=>["teamuser" => ["not found"]],
            // ])->response()->setStatusCode(404);
        }
    }
    protected function avoidDuplicate(int $teamUserId, string $mobile)
    {
        if (TeamUserMemmber::where("team_user_id", $teamUserId)->where("mobile", $mobile)->first())
            return false;
        return true;
    }
    protected function buyProductsForTeams(int $teamId, int $userId)
    {           
        $buying = new Buying;       
        $memmbers = TeamUserMemmber::where("team_user_id", $teamId)->with("member")->get();        
        $teamUserProductIds = self::getProductTeamId();       
        foreach ($memmbers as $memmber) {
          
            $order = $this->addOrder($memmber->member->id);           
            if ($order) {
                $OrderDetail = new OrderDetail;
                foreach ($teamUserProductIds as $teamUserProductId) {

                    $OrderDetail = [
                        "orders_id" => $order->id,
                        "products_id" => $teamUserProductId,
                        "price" => 0,
                        "coupons_id" => 0,
                        "users_id" => $order->users_id,
                        "all_videos_buy" => 1,
                        "number" => 1,
                        "total_price_with_coupon" => 0,
                        "total_price" => 0
                    ];
                    $userProduct=
                    [
                        "products_id" => $teamUserProductId,
                        "users_id" =>$order->users_id,
                        "partial" =>0,
                    ];
                    $this->orderDetailAdd($OrderDetail);
                    
                   //$this->userProductAdd($userProduct);
                }
                $buying->completeInsertAfterBuying(Order::find($order->id));
                $this->smsObj->sendCode($memmber->member->email,"زیست", "confirm-team-members");
            }
        }
        
        $leaderAddOrder = $this->addOrder($userId);     
        foreach ($teamUserProductIds as $teamUserProductId) {
            $OrderDetail = [
                "orders_id" =>  $leaderAddOrder->id,
                "products_id" => $teamUserProductId,
                "price" => 0,
                "coupons_id" => 0,
                "users_id" => $leaderAddOrder->users_id,
                "all_videos_buy" => 1,
                "number" => 1,
                "total_price_with_coupon" => 0,
                "total_price" => 0
            ];
            $userProduct=
            [
                "products_id" => $teamUserProductId,
                "users_id" =>$leaderAddOrder->users_id,
                "partial" =>0,
            ];
            $this->orderDetailAdd($OrderDetail);                     
        }
        $buying->completeInsertAfterBuying(Order::find($leaderAddOrder->id));
        $this->smsObj->sendCode(User::find($userId)->email,"زیست", "confirm-team-members");
       
        if ($leaderAddOrder) {
            // dd($orderobj->id);
            $this->addOrderDetails($leaderAddOrder->id);
        }
    }
    protected function orderDetailAdd($OrderDetail)
    {
       // dd($OrderDetail);
        return  OrderDetail::create($OrderDetail);
    } 
    protected function userProductAdd($userProduct)
    {
       // dd($OrderDetail);
        return  UserProduct::create($userProduct);
    }
    protected function addOrder(int $userId)
    {      
        $addMemmberOrder = $this->privateOrderController->_store($userId, true);
        if (!$addMemmberOrder) {
            $this->errorHandle("user id $userId", "not found to add Order ");
        }
        return $addMemmberOrder;
    }
    protected function addOrderDetails(int $userId)
    {    
        Route::name("addOrder", ["users_id" => $userId]);
        $Orderobj = Order::where("users_id", $userId)->where("status", "ok")->first();
        return $Orderobj;
    }
    protected static function getProductTeamId()
    {
        $teamUserProduct = TeamProductDefaults::all()->pluck("product_id");       
        return ($teamUserProduct);
    }
    protected function deleteTeam($id)
    {
       if(TeamUser::find($id)->delete())
       {
          $teamMembers= TeamUserMemmber::where("team_user_id",$id)->get();
           foreach($teamMembers as $member)
           {
                 $member->delete();
           }
       }

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

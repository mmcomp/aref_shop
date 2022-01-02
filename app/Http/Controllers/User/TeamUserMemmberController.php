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
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;

class TeamUserMemmberController extends Controller 
{ 
   private $privateOrderController;
   public function __construct(OrderController $orderController) 
    {
        $this->privateOrderController=$orderController;
    }
    public function index()
    {
        $data= TeamUserMemmberResource::collection(TeamUserMemmber::all());
        return $data;
    }

    public function store(TeamUserMemmberCreateRequest $teamUserMemmber)
    {
        $data="";
        $user=User::where('id',Auth::user()->id)->with("teamUser")->first();      
        $teamUserMemmber["is_verified"]=false;
        $teamUserMemmber["team_user_id"]=$user->teamUser->id;
        //$leaderexist=User::where("email",$teamUserMemmber["mobile"])->first();
        if($user)
        {
           if($this->avoidDuplicate($user->teamUser->id,$teamUserMemmber["mobile"]))
           {
            $data=TeamUserMemmber::create($teamUserMemmber->toArray());
           }
           else{
            $this->errorHandle("User","this mobile has alreade been added");
           }           
        }       
        return new TeamUserMemmberResource($data);
    }
    public function update(int $teamUserId)
    {               
       // $this->buyProductsForTeams(8784);
        //dd($request->mobile);
        //dd(Auth::user());
       // $user=User::where('id',Auth::user()->id)->with("teamUser")->first();
        // dd($user->email);
        // dd($teamUserId);
         $teamUserMemmberobj=TeamUserMemmber::where("mobile",Auth::user()->email)->where("team_user_id",$teamUserId)->first();
        //dd( $teamUserMemmberobj);
        if($teamUserMemmberobj)
        {
                        //$team_user_id=$teamUserMemmberobj["team_user_id"];           
            $this->updateTeamUserMemmber($teamUserMemmberobj);               
            if($this->isCountToEnd($teamUserId))
            {
                $this->updateTeamUser($teamUserId);         
            }
            // else{
            //     //dd("fsf");
            //     $this->updateTeamUser($teamUserId);
            // }   
            return response()->json($teamUserMemmberobj,200);            
        }
        else
        { 
            //dd("sdf");           
           $this->errorHandle("TeamUserMemmberResource","TeamUserMemmber not found or it is verified before");         
        }
    }
    public function destroyMemmber()
    {
        
    }
    public  function updateTeamUserMemmber(TeamUserMemmber $teamUserMemmber)
    {
        $teamUserMemmber["is_verified"]=true;
       // dd( $teamUserMemmber);
        $updatetd=$teamUserMemmber->update();
        if(!$updatetd)
        {
            $this->errorHandle("TeamUserMemmberResource","fail to update");                     
        }
          /////////////////////////////////           delete all of teams if this person in invited when the first invited avccept
             $this->delteMySelfFromAllTeam ($teamUserMemmber); 

    }
    protected function delteMySelfFromAllTeam(TeamUserMemmber $teamUserMemmber)
    {
        $teamUserMemmbers=TeamUserMemmber::where("mobile",$teamUserMemmber->mobile)->where("is_verified",0)->get();
          foreach($teamUserMemmbers as $memmber)
          {
              $memmber->delete();
          }
    }
    protected  function isCountToEnd(int $team_user_id)
    {
        $teamUserCount=TeamUserMemmber::where("team_user_id",$team_user_id)->where("is_verified",1)->count();
        if($teamUserCount >=2)
               return true;
        return false; 
    }
    public function updateTeamUser($team_user_id)
    {             
        $teamUser= TeamUser::find($team_user_id);
        //dd( $teamUser);
        if($teamUser!==null)
        {
            $teamUser["is_full"]=1;
            //$teamUser=$teamUser->update();
            if(!$teamUser->update())
            {
               // dd("fgfg");
              $this->errorHandle("TeamUser","fail to update");               
            }
            else
            {///////////////////////////////////  delete all invitation for all people for this group if it is full /////////
                //dd($teamUser->user_id_creator);
                $this->buyProductsForTeams($teamUser->id,$teamUser->user_id_creator);
                $teamUserMemmbers=TeamUserMemmber::where("team_user_id",$team_user_id)->where("is_verified",0)->get();
                foreach($teamUserMemmbers as $memmber)
                {
                    $memmber->delete();
                }            
                //dd($teamUserMemmber->toArray());                
               // dd($teamUserMemmber);
            }
        }
        else
        {
            $this->errorHandle("TeamUser","not found");
            // return (new TeamUserResource(null))->additional([
            //     "errors"=>["teamuser" => ["not found"]],
            // ])->response()->setStatusCode(404);
        }
        
    }
    protected function avoidDuplicate(int $teamUserId,string $mobile)
    {
       if( TeamUserMemmber::where("team_user_id",$teamUserId)->where("mobile",$mobile)->first())
        return false;
       return true;
    }
    protected function buyProductsForTeams(int $teamId,int $userId)
    { 
        //dd($userId);
        //$teamUserId= $userId;
        $memmbers=TeamUserMemmber::where("team_user_id",$teamId)->with("member")->get();
       //dd($memmbers[0]->member->id); 
        $teamUserProductIds=self::getProductTeamId();
        foreach($memmbers as $memmber)
        {
            //dump($memmber->member->id);
           $order= $this->addOrder($memmber->member->id);
           if( $order)
           {
               foreach($teamUserProductIds as $teamUserProductId)
               {
                OrderDetail::create([
                    "orders_id" => $order->id,
                    "products_id" => $teamUserProductId,
                    "price" =>0,
                    "coupons_id" => 0,
                    "users_id" =>$memmbers[0]->member->id,
                    "all_videos_buy" =>1,
                    "number" => 1,
                    "total_price_with_coupon" => 0,
                    "total_price" => 0
                ]);
               }
               
           }
        }   
       $leaderAddOrder= $this->addOrder($userId);  
      // $orderobj=$this->addOrder(4);
    //    dd($orderobj->toArray());
       if($leaderAddOrder)
       {
          // dd($orderobj->id);
           $this->addOrderDetails($leaderAddOrder->id);
       }
    }
    protected function addOrder(int $userId)
    { 
       
       // dd($teamUserProductIds);
        // $req= new InsertOrderForUserRequest;
        // $req->replace(["users_id" => $userId]);
       // dd($req);
       // $orderobj= $this->privateOrderController->store($req);
       //dd();
        $addMemmberOrder=$this->privateOrderController->_store($userId,true);
        if(!$addMemmberOrder)
        {
            $this->errorHandle("user id $userId" ,"not found to add Order ");
        }                  
        return $addMemmberOrder;
       
    }
    protected function addOrderDetails(int $userId)
    {   
        dd("order is don");
        Route::name("addOrder",["users_id"=>$userId]);  
        $Orderobj=Order::where("users_id",$userId)->where("status","manual_waiting")->first();
        return $Orderobj;
       
    }
    protected static function getProductTeamId()
    {
        $teamUserProduct=TeamUserProduct::all()->pluck("product_id");
        //dd($teamUserProduct);
        return($teamUserProduct);
    }
    public function errorHandle($class,$error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],
               
            ],422)
        );
    }

}

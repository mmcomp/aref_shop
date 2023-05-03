<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserMemberCreateRequest;
use App\Http\Resources\User\TeamUserMemberResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

use App\Models\TeamUserMember;
use App\Models\TeamUser;
use App\Models\UserProduct;
use App\Models\TeamProductDefaults;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Utils\Buying;
use App\Utils\Sms;
use Log;


class TeamUserMemberController extends Controller
{
    private $privateOrderController;
    private $smsObj;
    public function __construct(OrderController $orderController)
    {
        $this->smsObj = new Sms;
        $this->privateOrderController = $orderController;
    }
    public function index()
    {
        $data = TeamUserMemberResource::collection(TeamUserMember::all());
        return $data;
    }

    public function store(TeamUserMemberCreateRequest $teamUserMember)
    {
        $user = User::where('id', Auth::user()->id)->with("teamUser")->first();
        $isLeader = $this->isLeader($teamUserMember["mobile"]);
        if ($isLeader) {
            if ($user->teamUser) {
                $this->deleteTeam($user->teamUser->id);
            }
            //$this->errorHandle("Leader", " شماره ".$teamUserMember["mobile"]."برای سرگروه می باشد.");
            $this->errorHandle("TeamUser", " شماره " . $teamUserMember["mobile"] . " قبلا به عنوان عضو درج شده");
        }
        if ($user->teamuser !== null) {
            $exist = TeamUserMember::where("mobile", $teamUserMember["mobile"])->where("is_verified", 1)->first();
            if ($exist) {
                $this->deleteTeam($user->teamUser->id);
                $this->errorHandle("TeamUser", " شماره " . $teamUserMember["mobile"] . " قبلا به عنوان عضو درج شده");
            }
            $data = "";
            //$userFullNmae=str_replace(' ',"-",$user->first_name ."-". $user->last_name);
            $teamUserMember["is_verified"] = false;

            if ($user && $user->teamuser !== null) {
                $teamUserMember["team_user_id"] = $user->teamUser->id;
                if ($this->avoidDuplicate($user->teamUser->id, $teamUserMember["mobile"])) {
                    $data = TeamUserMember::create($teamUserMember->toArray());
                    $this->notifyToNotApprovedMembers($user->teamUser->id, $user);
                
                } else {
                    $this->deleteTeam($user->teamUser->id);
                    $this->errorHandle("User", "شماره " . $teamUserMember['mobile'] . " تکراری است");
                }
            }
        } else {
            $this->errorHandle("TeamUser", "لطفا ابتدا تیم را ایجاد کنید.");
        }
        return new TeamUserMemberResource($data);
    }
    public function update(int $teamUserId)
    {
        $teamUserMemberobj = TeamUserMember::where("mobile", Auth::user()->email)->where("team_user_id", $teamUserId)->first();

        if ($teamUserMemberobj) {
            //$team_user_id=$teamUserMemberobj["team_user_id"];           
            $this->updateTeamUserMember($teamUserMemberobj);
            if ($this->isCountToEnd($teamUserId)) {
                $this->updateTeamUser($teamUserId);
            }
            // else{
            //    
            //     $this->updateTeamUser($teamUserId);
            // }   
            return response()->json($teamUserMemberobj, 200);
        } else {

            $this->errorHandle("TeamUserMemberResource", " شخص مورد نظر پیدا نشد یا اینکه قبلا عضو یک تیم بوده است.");
        }
    }
    public function destroyMember()
    {
    }
    public function isLeader(string $mobile)
    {
        $user = User::where("email", $mobile)->first();

        if ($user !== null) {
            $response = TeamUser::where("user_id_creator", $user->id)->with("leader")->first();
            if ($response) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
    public  function updateTeamUserMember(TeamUserMember $teamUserMember)
    {
        $teamUserMember["is_verified"] = true;
        $updatetd = $teamUserMember->update();
        if (!$updatetd) {
            $this->errorHandle("TeamUserMemberResource", "ویرایش اطلاعات با موفقیت همراه نبود.");
        }
        /////////////////////////////////           delete all of teams if this person in invited when the first invited avccept
        $this->delteMySelfFromAllTeam($teamUserMember);
    }
    protected function delteMySelfFromAllTeam(TeamUserMember $teamUserMember)
    {
        $teamUserMembers = TeamUserMember::where("mobile", $teamUserMember->mobile)->where("is_verified", 0)->get();
        foreach ($teamUserMembers as $Member) {
            $Member->delete();
        }
    }
    protected  function isCountToEnd(int $team_user_id)
    {
        $teamUserCount = TeamUserMember::where("team_user_id", $team_user_id)->where("is_verified", 1)->count();
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
                $this->errorHandle("TeamUser", "ویرایش اطلاعات با موفقیت همراه نبود.");
            } else { ///////////////////////////////////  delete all invitation for all people for this group if it is full /////////

                $this->buyProductsForTeams($teamUser->id, $teamUser->user_id_creator);
                $teamUserMembers = TeamUserMember::where("team_user_id", $team_user_id)->where("is_verified", 0)->get();
                foreach ($teamUserMembers as $Member) {
                    $Member->delete();
                }
            }
        } else {
            $this->errorHandle("TeamUser", "تیم مورد نظر یافت نشد.");
            // return (new TeamUserResource(null))->additional([
            //     "errors"=>["teamuser" => ["not found"]],
            // ])->response()->setStatusCode(404);
        }
    }
    protected function avoidDuplicate(int $teamUserId, string $mobile)
    {
        if (TeamUserMember::where("team_user_id", $teamUserId)->where("mobile", $mobile)->first())
            return false;
        return true;
    }
    protected function buyProductsForTeams(int $teamId, int $userId)
    {
        $buying = new Buying;
        $Members = TeamUserMember::where("team_user_id", $teamId)->with("member")->get();
        $teamUserProductIds = self::getProductTeamId();
        foreach ($Members as $Member) {

            $order = $this->addOrder($Member->member->id);
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
                    $userProduct =
                        [
                            "products_id" => $teamUserProductId,
                            "users_id" => $order->users_id,
                            "partial" => 0,
                        ];
                    $this->orderDetailAdd($OrderDetail);

                    //$this->userProductAdd($userProduct);
                }
                $buying->completeInsertAfterBuying(Order::find($order->id));
                $this->smsObj->sendCode($Member->member->email, "عارف", "confirm-team-members");
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
            $userProduct =
                [
                    "products_id" => $teamUserProductId,
                    "users_id" => $leaderAddOrder->users_id,
                    "partial" => 0,
                ];
            $this->orderDetailAdd($OrderDetail);
        }
        $buying->completeInsertAfterBuying(Order::find($leaderAddOrder->id));
        $this->smsObj->sendCode(User::find($userId)->email, "عارف", "confirm-team-members");

        if ($leaderAddOrder) {

            $this->addOrderDetails($leaderAddOrder->id);
        }
    }
    protected function orderDetailAdd($OrderDetail)
    {

        return  OrderDetail::create($OrderDetail);
    }
    protected function userProductAdd($userProduct)
    {

        return  UserProduct::create($userProduct);
    }
    protected function addOrder(int $userId)
    {
        $addMemberOrder = $this->privateOrderController->_store($userId, true);
        if (!$addMemberOrder) {
            $this->errorHandle("user id $userId", "عضو مورد نظر برای درج سفارش یافت نشد.");
        }
        return $addMemberOrder;
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
        if (TeamUser::find($id)->delete()) {
            $teamMembers = TeamUserMember::where("team_user_id", $id)->get();
            foreach ($teamMembers as $member) {
                $member->delete();
            }
        }
    }
    protected function notifyToNotApprovedMembers(int $teamUserId,User $user)
    {
        $allNotApprovedMembers = TeamUserMember::where("team_user_id", $teamUserId)
            ->with("member")
            ->where("is_verified", 0)
            ->get();
        if (count($allNotApprovedMembers) >= 2) {
            if (isset($user->first_name) || isset($user->last_name))
                $userFullName = str_replace(' ', "-", $user->first_name . "-" . $user->last_name);
            else
                $userFullName = "شخصی";
            foreach ($allNotApprovedMembers as $allNotApprovedMember) {
                $this->smsObj->sendCode($allNotApprovedMember->mobile,   $userFullName, 'verify-team-member');
            }
            return true;
        } else {
            //$this->errorHandle("TeamUserMember", "fail to update");
            return false;
        }
    }
    // $this->teamIsFull($user->teamUser->id);
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }
}

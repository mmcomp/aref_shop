<?php

namespace App\Http\Controllers;

use App\Http\Requests\getWholeCartRequest;
use App\Http\Requests\InsertOrderForUserRequest;
use App\Http\Requests\StoreProductOrderDetailRequest;
use App\Http\Requests\DeleteMicroProductFromCartRequest;
use App\Http\Requests\DeleteProductFromCartRequest;
use App\Http\Requests\AddMicroProductToCartRequest;
use App\Http\Requests\storeProductByMobileListRequest;
use App\Http\Requests\storeProductPackageRequest;

use App\Utils\RaiseError;
use App\Models\Order;
use App\Models\User;
use App\Models\Refund;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\OrderVideoDetail;
use App\Models\ProductDetailVideo;
use App\Models\ProductDetailPackage;
use App\Models\Coupon;
use App\Models\UserProduct;
use App\Models\UserVideoSession;
use App\Models\ProductDetailChair;
use App\Models\OrderChairDetail;

use App\Http\Requests\AddCouponToTheCartRequest;
use App\Http\Requests\CancelBuyingOfAMicroProductRequest;
use App\Http\Requests\CompleteBuyingRequest;
use App\Http\Requests\deleteCouponFromCartRequest;
use App\Http\Requests\DestroyWholeCartRequest;
use App\Http\Requests\CancelBuyingOfARequest;
use Illuminate\Support\Facades\DB;
use App\Models\UserCoupon;
use App\Models\Audit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Resources\AdminOrderResource;
use App\Http\Resources\GetInfoOfAnOrderResource;
use App\Http\Resources\User\OrderResource;
use App\Utils\Buying;
use App\Utils\buyProductsAccordingUserMobile;
use Illuminate\Http\Request;
use App\Utils\AdminLog;

class OrderController extends Controller
{

    /**
     * get info of an order
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfoOfAnOrder(int $id)
    {
        $order = Order::whereId($id)->with('orderDetails.orderChairDetails')->first();
        if ($order != null) {
            return (new GetInfoOfAnOrderResource($order))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new GetInfoOfAnOrderResource(null))->additional([
            'errors' => ['order' => ['Order does not exist!']],
        ])->response()->setStatusCode(406);
    }
    public function storeProductByMobileList(storeProductByMobileListRequest $request)
    {
        $user_ids = $this->convertUserMobileToId($request->mobile_list);
        $buyed = array();
        $beforBuyed = array();
        $notRegistered = array();
        $result = array();
        $id = 5;
        $utilObject = new buyProductsAccordingUserMobile;
        foreach ($request->products_id as $product_id) {
            $id++;
            foreach ($user_ids["exist"] as $user_id) {
                $found_user_product_zeroPartial = $this->checkUserProduct($user_id, $product_id, 0);
                if ($found_user_product_zeroPartial) {
                    $beforBuyed[] = $user_id;
                    continue;
                }
                // $addorder[]=$user_id;
                $order = $utilObject->AddToOrder($user_id, $product_id, 1, "خرید محصول با استفاده از موبایل کاربر");

                if (!$order) {
                    $notRegistered[] = $user_id;
                    continue;
                    // return (new AdminOrderResource(null))->additional([
                    //     'errors' => ['order' => ['The order can not registered! maybe user is admin.']],
                    // ])->response()->setStatusCode(406);
                }

                $orderDetails = $utilObject->orderDetails($product_id, $order->id);
                if (!$orderDetails) {
                    $notRegistered[] = $user_id;
                    continue;
                    // return (new AdminOrderResource(null))->additional([
                    //     'errors' => ['orderDetails' => ['The order can not registered!']],
                    // ])->response()->setStatusCode(406); 

                }
                $found_user_product_partial = $this->checkUserProduct($user_id, $product_id, 1);
                if ($found_user_product_partial) {
                    $found_user_product_partial->delete(); /// delete if user buy one  session of a product
                }
                $resultOrder = $utilObject->completeBuying($order->id, 0, "test a buying");
                if (!$resultOrder) {
                    $notRegistered[] = $user_id;
                    continue;
                    // return (new AdminOrderResource(null))->additional([
                    //     'errors' => ['complete buying' => ['The complete buying can not registered!']],
                    // ])->response()->setStatusCode(406); 
                }
                $buyed[] = $user_id;
            }

            $user_mobiles["buyed"] = $this->convertUserIdToMobile($buyed);
            $user_mobiles["beforBuyed"] = $this->convertUserIdToMobile($beforBuyed);
            $user_mobiles["notRegistered"] = $this->convertUserIdToMobile($notRegistered);

            //$result=array($product_id => array("buyed" =>  $user_mobiles["buyed"]->toArray()));
            $result[$id]["buyed"] = $user_mobiles["buyed"]->toArray(); //$buyed;
            $result[$id]["notexists"] = $user_ids["notexist"];
            $result[$id]["notRegistered"] = $user_mobiles["notRegistered"]->toArray(); // $notRegistered;
            $result[$id]["beforBuyed"] = $user_mobiles["beforBuyed"]->toArray();
        }

        return $result;
    }

    public function convertUserIdToMobile(array $user_ids)
    {
        $user_mobile = User::whereIn("id", $user_ids)->pluck("email");
        return $user_mobile;
    }
    public function checkUserProduct(int $user_id, int $product_id, int $partial)
    {
        $found_user_product = UserProduct::where('users_id', $user_id)
            ->where('products_id', $product_id)
            ->where('partial', $partial)
            ->first();
        return $found_user_product;
    }
    public function convertUserMobileToId($mobiles)
    {
        $notexist = array();
        $exists = array();
        $total = array();

        foreach ($mobiles as $mobile) {
            $isFounded = User::where("email", $mobile)->first();
            if (!$isFounded) {
                $notexist[] = $mobile;
                continue;
            }
            $exists[] = $isFounded->id;
        }
        $total["exist"] = $exists;
        $total["notexist"] = $notexist;
        return $total;
    }
    public function store(InsertOrderForUserRequest $request, bool $addteamOrder = false)
    {  

        $users_id = $request->input('users_id');
        $response = $this->_store($users_id, $addteamOrder);
        
        if ($response === null) {
            return (new AdminOrderResource(null))->additional([
                'errors' => ['type' => ['The user type is invalid!']],
            ])->response()->setStatusCode(406);
        }
        return (new AdminOrderResource($response))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
    /**
     * Insert factor for a user
     *
     * @param  \App\Http\Requests\InsertOrderForUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _store($users_id, $addteamOrder)
    {
        //$users_id = $request->input('users_id');
        $user = User::where('is_deleted', false)->find($users_id);
        if ($user->group->type == 'user') {
            $comment = ($addteamOrder == true ? "خرید خودکار محصول برای اعضای تیم" : "");
            $saverUsersId = ($addteamOrder == true ? $users_id : Auth::user()->id);
            $order = Order::where('users_id', $users_id)->where('status', 'manual_waiting')->first();
            if ($order == null) {
                $order = Order::create([
                    'users_id' => $users_id,
                    'saver_users_id' => $saverUsersId,
                    'status' => ($addteamOrder == true ? "ok" : "manual_waiting"),
                    'comment' => $comment,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }

            return $order;
            // return (new AdminOrderResource($order))->additional([
            //     'errors' => null,
            // ])->response()->setStatusCode(201);
        }
        return null;
        // return (new AdminOrderResource(null))->additional([
        //     'errors' => ['type' => ['The user type is invalid!']],
        // ])->response()->setStatusCode(406);
    }
    /**
     * add orderDetail product
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\StoreProductOrderDetailRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProduct(StoreProductOrderDetailRequest $request, $orders_id)
    {
        $number = $request->input('number', 1);
        $products_id = $request->input('products_id');
        $order = Order::find($orders_id);
        $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();

        //dd($orderDetail);
        // $alreadyProductExist=OrderDetail::where('all_videos_buy',1)
        // ->where('users_id',$order->users_id)
        // ->where('products_id',$products_id)
        // ->first();
        $existAlready=null;
       // if($alreadyProductExist)
       // {
            $existAlready=UserProduct::where('users_id',$order->users_id)
            ->where('products_id',$products_id)
            ->where('partial',0)
            ->first();
       // }
       


        if($existAlready){
            return (new OrderResource($order))->additional([
                'errors' => ["product already exist" => ["The product code has already been exist."]],
            ])->response()->setStatusCode(409);
            
        }

        if ($orderDetail && $product->type == 'normal') {
            $orderDetail->number += $number;
            $orderDetail->total_price = $orderDetail->number * $orderDetail->price;
            $orderDetail->total_price_with_coupon = $orderDetail->total_price;
            $orderDetail->save();
        } else if ($orderDetail && $product->type == 'video' && !$orderDetail->all_videos_buy) {
            $orderDetail->all_videos_buy = 1;
            OrderVideoDetail::where('order_details_id', $orderDetail->id)->delete();
            $order = Order::find($orders_id);
            $orderDetail->save();
        } else if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $order->users_id,
                'all_videos_buy' => 1,
                'number' => $product->type != 'normal' ? 1 : $number,
                'total_price' => DB::raw('number * price'),
                'total_price_with_coupon' => DB::raw('number * price')
            ]);
        }
        $orderDetailPricesArraySum = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->amount = $orderDetailPricesArraySum;
        $order->save();
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * add a microProduct to the cart
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\AddMicroProductToCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function StoreMicroProduct(AddMicroProductToCartRequest $request, $orders_id)
    {        
        $raiseError = new RaiseError;
        $products_id = $request->input('products_id');
        $product_details_id = $request->input('product_details_id');
        $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        $orderDetail = OrderDetail::where('orders_id', $orders_id)->where('products_id', $products_id)->first();
        $order = Order::find($orders_id);
        if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $order->users_id,
                'number' => 1,
                'total_price' => DB::raw('number * price'),
                'total_price_with_coupon' => DB::raw('number * price')
            ]);
        } else if ($orderDetail && $orderDetail->all_videos_buy) {
            return (new OrderResource(null))->additional([
                'errors' => ['added_before' => ['already added!']],
            ])->response()->setStatusCode(406);
        }

        if ($product->type == 'video') {

            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('id', $product_details_id)->where('products_id', $products_id)->first();           
            //$existalready=UserProduct::where('users_id',$order->users_id)->where('products_id',$product_detail_video->products_id)->first();
            $existalready=UserVideoSession::where('users_id',$order->users_id)
            ->where('video_sessions_id',$product_detail_video->video_sessions_id)            
            ->first();
            //dd($existalready);
            if($existalready){
            
                return (new OrderResource($order))->additional([
                    'errors' => ["session already exist" => ["The session code has already been applied."]],
                ])->response()->setStatusCode(409);
                
            }

            $raiseError->ValidationError($product_detail_video == null, ['product_detail_videos_id' => ['The product_details_id is not valid!']]);
            $found_order_video_detail = OrderVideoDetail::where('order_details_id', $orderDetail->id)->where('product_details_videos_id', $product_details_id)->first();
            if (!$found_order_video_detail) {
                OrderVideoDetail::create([
                    'order_details_id' => $orderDetail->id,
                    'product_details_videos_id' => $product_details_id,
                    'price' => $product_detail_video->price,
                    'number' => 1,
                    'total_price' => DB::raw('number * price'),
                    'total_price_with_coupon' => DB::raw('number * price')
                ]);
            }
            $sumOfOrderVideoDetailPrices = OrderVideoDetail::where('order_details_id', $orderDetail->id)->sum('price');
            $orderDetail->total_price = $sumOfOrderVideoDetailPrices;
            $orderDetail->total_price_with_coupon = $sumOfOrderVideoDetailPrices;
            $orderDetail->price = $orderDetail->total_price_with_coupon;
            $orderDetail->save();
        } else if ($product->type == 'chairs') {

            foreach ($request->chairs as $chair) {
                $chair_price = ProductDetailChair::where('start', '<=', $chair)
                    ->where('end', '>=', $chair)
                    ->where('products_id', $products_id)
                    ->select('price')
                    ->first();

                if ($chair_price === null)
                    $chair_price = -1;
                else
                    $chair_price = $chair_price["price"];

                if ($chair_price > -1) // in valid chair number insert in another table
                {
                    $order_chair_detail = OrderChairDetail::firstOrCreate(
                        [
                            "order_details_id" => $orderDetail->id,
                            "chair_number"     => $chair,
                        ],
                        [
                            "order_details_id" =>  $orderDetail->id,
                            "chair_number"     => $chair,
                            "price" => $chair_price
                        ]
                    );
                }
            }
            $add_chair_price = self::updateVideoDetailChairPrice($orderDetail->id);
        }
        $sumOfOrderDetailPrices = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->amount = $sumOfOrderDetailPrices;
        $order->save();
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\getWholeCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWholeCart(getWholeCartRequest $request, $orders_id)
    {

        $order = Order::find($orders_id);
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * add coupon to the cart
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\AddCouponToTheCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function addCouponToTheCart(AddCouponToTheCartRequest $request, $orders_id)
    {

        $raiseError = new RaiseError;
        $coupon = Coupon::where('is_deleted', false)->where('name', $request->input('coupons_name'))->first();
        $products_id = $coupon->products_id;
        $order = Order::find($orders_id);
        $raiseError->ValidationError($order == null, ['orders_id' => ['There is not any waiting orders yet for the user!']]);
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        $raiseError->ValidationError($orderDetail == null, ['products_id' => ['The user does not have any orders for the product that he/she has coupon for']]);
        $user_coupon = UserCoupon::where('users_id', $order->users_id)->where('coupons_id', $coupon->id)->first();
        if ($user_coupon) {
            return (new OrderResource(null))->additional([
                'errors' => ["already applied" => ["The discount code has already been applied."]],
            ])->response()->setStatusCode(406);
        } else if ($coupon->expired_at != null && $coupon->expired_at < Carbon::now()->format('Y-m-d')) {
            return (new OrderResource(null))->additional([
                'errors' => ["expired" => ["The discount code has been expired"]],
            ])->response()->setStatusCode(406);
        }
        if ($orderDetail->all_videos_buy) {

            $orderDetail->coupons_id = $coupon->id;
            if ($coupon->type == 'amount') {
                $raiseError->ValidationError($coupon->amount >= $orderDetail->total_price, ['amount' => ['The coupon amount(' . $coupon->amount . ')should be less than the total_price(' . $orderDetail->total_price . ')']]);
                $orderDetail->total_price_with_coupon = $orderDetail->total_price - $coupon->amount;
                $orderDetail->coupons_type = 'amount';
            } else if ($coupon->type == 'percent') {
                $orderDetail->total_price_with_coupon = $orderDetail->total_price - (($coupon->amount / 100) * $orderDetail->total_price);
                $orderDetail->coupons_type = 'percent';
            }

            $orderDetail->coupons_amount = $coupon->amount;
            try {
                $orderDetail->save();
                $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
                $order->save();
                UserCoupon::create([
                    'users_id' => $order->users_id,
                    'coupons_id' => $coupon->id
                ]);
                return (new OrderResource($order))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info("fails in addCouponToTheCart in User/CartController" . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in addCouponToTheCart in User/CartController" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in addCouponToTheCart in User/CartController"]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new OrderResource(null))->additional([
            "errors" => ["all_videos_buy" => ["Coupon can not be used when you didn't buy all of a product!"]]
        ])->response()->setStatusCode(406);
    }
    /**
     * delete coupon from cart
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\AddCouponToTheCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCouponFromCart(DeleteCouponFromCartRequest $request, $orders_id)
    {

        $raiseError = new RaiseError;
        $coupon = Coupon::where('is_deleted', false)->where('name', $request->input('coupons_name'))->first();
        $products_id = $coupon->products_id;
        $order = Order::find($orders_id);
        $raiseError->ValidationError($order == null, ['orders_id' => ['You don\'t have any waiting orders yet!']]);
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        $raiseError->ValidationError($orderDetail == null, ['products_id' => ['You don\'t have any orders for the product that you have coupon for']]);
        if ($orderDetail->coupons_id && $orderDetail->coupons_amount != null) {
            $orderDetail->coupons_id = 0;
            $orderDetail->total_price_with_coupon = $orderDetail->total_price;
            $orderDetail->coupons_amount = null;
            $orderDetail->coupons_type = "";
            try {
                $orderDetail->save();
                $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
                $order->save();
                return (new OrderResource($order))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info("fails in deleteCouponToTheCart in Admin/OrderController" . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in deleteCouponToTheCart in Admin/OrderController" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in deleteCouponToTheCart in Admin/OrderController"]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
    }

    /**
     * destroy whole cart
     *
     * @param int $orders_id
     * @param  \App\Http\Requests\User\DestroyWholeCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyWholeCart(DestroyWholeCartRequest $request, $orders_id)
    {

        $order = Order::find($orders_id);
        $order->status = 'manual_waiting';
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $item) {
                $item->where('orders_id', $order->id)->delete();
                OrderVideoDetail::where('order_details_id', $item->id)->delete();
            }
        }
        try {
            $order->save();
            return (new OrderResource($order))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        } catch (Exception $e) {
            Log::info('failed in Admin/OrderController/destoryWholeCart', json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new OrderResource(null))->additional([
                    'errors' => ["fail" => 'destroying Whole Cart failed!' . json_encode($e)],
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new OrderResource(null))->additional([
                    'errors' => ['fail' => ['destroying Whole Cart failed!']],
                ])->response()->setStatusCode(500);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\DeleteProductFromCartRequest  $request
     * @param  int  $order_details_id
     * @param  int  $orders_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($orders_id, $order_details_id,  DeleteProductFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $orderDetail = OrderDetail::where('id', $order_details_id)->first();
        $order = Order::find($orders_id);
        $foundOrderDetail = OrderDetail::where('orders_id', $orders_id)->find($order_details_id);
        $raiseError->ValidationError(!$foundOrderDetail, ['orders_id' => ['The orders id and order details id are not related to eachother!']]);
        OrderDetail::where('id', $order_details_id)->delete();
        if ($orderDetail->product->type == 'video') {
            if (!$orderDetail->all_videos_buy) {
                OrderVideoDetail::where('order_details_id', $order_details_id)->delete();
            }
        }
        $order = Order::where('id', $orderDetail->orders_id)->first();
        $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->save();
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\User\DeleteMicroProductFromCartRequest  $request
     * @param  int  $order_details_id
     * @param  int  $orders_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMicroProduct($orders_id, $order_details_id,  DeleteMicroProductFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $product_details_id = $request->input('product_details_id');
        $orderDetail = OrderDetail::find($order_details_id);
        $order = Order::find($orders_id);
        $foundOrderDetail = OrderDetail::where('orders_id', $orders_id)->find($order_details_id);
        $raiseError->ValidationError(!$foundOrderDetail, ['orders_id' => ['The orders id and order details id are not related to eachother!']]);
        $raiseError->ValidationError($orderDetail->order->user->id != $order->users_id, ['users_id' => ['This is order of another user!']]);
        $raiseError->ValidationError($orderDetail->product->type == 'video' && $orderDetail->all_videos_buy, ['all_videos_buy' => ['The User already bought ' . $orderDetail->product->name . ' therefore he/she can not remove a subproduct of it']]);
        if ($orderDetail->product->type == 'video' && !$orderDetail->all_videos_buy) {
            $order_video_details = OrderVideoDetail::where('order_details_id', $order_details_id)->pluck('product_details_videos_id')->toArray();
            $raiseError->ValidationError(!in_array($product_details_id, $order_video_details), ['product_details_id' => ['The product_details_id is not valid!']]);
            OrderVideoDetail::where('order_details_id', $order_details_id)->where('product_details_videos_id', $product_details_id)->delete();
            $found = OrderVideoDetail::where('order_details_id', $order_details_id)->count();
            if (!$found) {
                OrderDetail::where('id', $order_details_id)->delete();
                $order = Order::where('users_id', $order->users_id)->where('status', '!=', 'cancel')->with('orderDetails')->first();
            }
        }
        $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->save();
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function destroyChairMicroProduct($id)
    {

        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', 'manual_waiting')->first();

        $orderChairDetail = OrderChairDetail::whereId($id)->first();
        if ($orderChairDetail !== null) {
            $orderDetailId = $orderChairDetail->order_details_id;
            //$chair_price=$orderChairDetail->price;
            if ($orderDetailId !== null) {
                $adminLog = new AdminLog;
                $OrderChairDetail = OrderChairDetail::whereId($id)->first();

                // these two line record user that deleted table record
                $OrderChairDetail = $OrderChairDetail->getTable()  .  $OrderChairDetail;
                $response = $adminLog->addLog($user_id, (string)$OrderChairDetail, "delete");

                OrderChairDetail::whereId($id)->delete();
                $del_price_chair = self::updateVideoDetailChairPrice($orderDetailId);
                $count = OrderChairDetail::where('order_details_id', $orderDetailId)->count();
                if ($count == 0) {

                    OrderDetail::whereId($orderDetailId)->delete();
                }
                $order_detail = OrderDetail::where('id', $orderDetailId)->first();
                if ($order_detail !== null) {
                    $sumOfOrderDetailPrices = OrderDetail::where('orders_id', $order_detail->orders_id)->sum('total_price_with_coupon');
                    $order->amount = $sumOfOrderDetailPrices;
                    //$order->save();
                } else {
                    $order->amount = 0;
                    // $order->save();
                }
            }
        }
        $order->save();
        return response([
            'errors' => null,
        ])->setStatusCode(201);
    }

    public function destroyChairMicroProductWithChairNumber($productId, $chairNumber)
    {
        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $activeOrder = Order::where('users_id', Auth::user()->id)
            ->where('status', 'waiting')
            ->first();
        $orderDetail = OrderDetail::where('products_id', $productId)
            ->where('orders_id', $activeOrder->id)
            ->first();
        // $price= updateVideoDetailChairPrice($order_detail_id,true);
        if ($orderDetail !== null) {
            $order_chair_detail_deleted = OrderChairDetail::where('order_details_id', $orderDetail->id)
                ->where('chair_number', $chairNumber)
                ->delete();
            $del_price_chair = self::updateVideoDetailChairPrice($orderDetail->id);
            $count = OrderChairDetail::where('order_details_id', $orderDetail->id)->count();
            if ($count == 0) {
                OrderDetail::whereId($orderDetail->id)->delete();
            }
            $sumOfOrderDetailPrices = OrderDetail::where('orders_id', $orderDetail->orders_id)->sum('total_price_with_coupon');
            $order->amount = $sumOfOrderDetailPrices;
            $order->save();
        }


        return response([
            'errors' => null,
        ])->setStatusCode(201);
    }
    /**
     * complete buying
     *
     * @param  \App\Http\Requests\CompleteBuyingRequest  $request
     * @param int $orders_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeBuying(CompleteBuyingRequest $request, $orders_id)
    {

        $order = Order::find($orders_id);
        $amount = $request->input('amount');
        $description = $request->input('description');
        $buying = new Buying;
        $order->status = "manual_ok";
        $order->amount = $amount;
        $order->comment = $description;
        $order->save();
        $buying->completeInsertAfterBuying($order);
        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
    /**
     * cancel buying a product
     *
     * @param  \App\Http\Requests\CancelBuyingOfARequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBuyingOfAProduct(CancelBuyingOfARequest $request)
    {

        $products_id = $request->input('products_id');
        $users_id = $request->input('users_id');
        $order_id = $request->input('order_id');
        // $product_detail_videos_id = $request->input('product_detail_videos_id');
        $product = Product::where('is_deleted', false)->find($products_id);
        // $order = Order::where('users_id', $users_id)->whereHas('orderDetails', function ($query) use ($products_id) {
        //     $query->where('products_id', $products_id);
        // })->where(function ($query) {
        //     $query->where('status', 'ok')->orWhere('status', 'manual_ok');
        // })->orderBy('updated_at', 'desc')->first();
        $order = Order::where('id', $order_id)->first();
        //dd($order);
        if ($order) {
            $orderDetail = OrderDetail::where('products_id', $products_id)->where('orders_id', $order->id)->first();
            // OrderDetail::where('products_id', $products_id)->where('orders_id', $order->id)->delete();
            if ($product->type == "package") {

                $tmp_child_products_id = ProductDetailPackage::where('is_deleted', false)
                    ->where('products_id', $products_id)
                    // ->whereNotIn('products_id', $products_id)
                    ->pluck('child_products_id');

               // dump( $tmp_child_products_id);

            //     $otherOrdersHaveTheSameProducts = Order::where('users_id', $users_id)
            //         ->whereHas('orderDetails', function ($query) use ($tmp_child_products_id) {
            //             $query->whereIn('products_id', $tmp_child_products_id);
            //         })->where(function ($query) {
            //             $query->where('status', 'ok')->orWhere('status', 'manual_ok');
            //         })->get();

            //     //dump($otherOrdersHaveTheSameProducts);
            //     $refunds = Refund::whereIn('orders_id', $otherOrdersHaveTheSameProducts->pluck('id'))->where('is_deleted', 0)->pluck('orders_id')->toArray();
            //    // dump($refunds);
            //     $notToDeleteOrders = [];
            //     foreach($otherOrdersHaveTheSameProducts as $otherOrdersHaveTheSameProduct) {
            //         if (!in_array($otherOrdersHaveTheSameProduct->id, $refunds)) {
            //             $notToDeleteOrders[] = $otherOrdersHaveTheSameProduct;
            //         }
            //     }

            //     $notToDeleteProducts = [];
            //     foreach($notToDeleteOrders as $notToDeleteOrder) {
            //         foreach($notToDeleteOrder->orderDetails as $orderDet) {
            //             $notToDeleteProducts[] = $orderDet->products_id;
            //         }
            //     }

            //     //dump($notToDeleteProducts);
            //     $child_products_id = [];
            //     foreach($tmp_child_products_id->toArray() as $pr) {
            //         if (!in_array($pr, $notToDeleteProducts)) {
            //             $child_products_id[] = $pr;
            //         }
            //     }
            //     //dd($child_products_id);       
            
            $child_products_id=$tmp_child_products_id;
            //dd($child_products_id); 

                if(count($child_products_id)>0) // the products that exist in package and not exist in another product that student bought already
                {
                    UserProduct::where('users_id', $users_id)->whereIn('products_id', $child_products_id)->where('partial', 0)->delete();
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->whereIn('products_id', $child_products_id)->pluck('video_sessions_id');
                    UserVideoSession::where('users_id', $users_id)->whereIn('video_sessions_id', $videoSessionIds)->delete();
                }
                
            } else {
                $parentProductIds = ProductDetailPackage::where('is_deleted', false)->where('child_products_id', $products_id)->pluck('products_id');
                $otherparentProductOrderIds = Order::where('users_id', $users_id)
                    ->whereHas('orderDetails', function ($query) use ($parentProductIds) {
                        $query->whereIn('products_id', $parentProductIds);
                    })->where(function ($query) {
                        $query->where('status', 'ok')->orWhere('status', 'manual_ok');
                    })->pluck('id');
                $refunds = Refund::whereIn('orders_id', $otherparentProductOrderIds)->where('is_deleted', 0)->count();
                $otherparentProductOrderCount = count($otherparentProductOrderIds) - $refunds;


                $otherOrderIds = Order::where('users_id', $users_id)
                    ->whereHas('orderDetails', function ($query) use ($products_id) {
                        $query->where('products_id', $products_id);
                    })->where('id', '!=', $order->id)->where(function ($query) {
                        $query->where('status', 'ok')->orWhere('status', 'manual_ok');
                    })->pluck('id');
                $refunds = Refund::whereIn('orders_id', $otherOrderIds)->where('is_deleted', 0)->count();
                $otherOrderCount = count($otherOrderIds) - $refunds;
                 //dump($otherOrderIds);
                // dump($refunds);
                //dd($otherparentProductOrderCount . "----". $otherOrderCount);  
                  

                if ($otherparentProductOrderCount === 0 )//&& $otherOrderCount === 0)
                {
                    UserProduct::where('users_id', $users_id)->where('products_id', $products_id)->where('partial', 0)->delete();
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $products_id)->pluck('video_sessions_id');
                    UserVideoSession::where('users_id', $users_id)->whereIn('video_sessions_id', $videoSessionIds)->delete();
                }
                
            }
            $found_refund = Refund::where('users_id', $users_id)->where('products_id', $products_id)->where('orders_id', $order->id)->first();
            if (!$found_refund) {
                Refund::create([
                    'users_id' => $users_id,
                    'products_id' => $products_id,
                    'saver_users_id' => Auth::user()->id,
                    'order_details_id' => $orderDetail->id,
                    'orders_id' => $order->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
            return (new OrderResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new OrderResource(null))->additional([
            'errors' => ['not_found' => ['order not found']],
        ])->response()->setStatusCode(404);
    }


    /**
     * cancel buying a video_session
     *
     * @param  \App\Http\Requests\CancelBuyingOfAMicroProductRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBuyingOfAMicroProduct(CancelBuyingOfAMicroProductRequest $request)
    {
        $raiseError = new RaiseError;
        $products_id = $request->input('products_id');
        $users_id = $request->input('users_id');
        // $order_id = $request->input('order_id');
        $product_detail_videos_id = $request->input('product_detail_videos_id');
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($product_detail_videos_id);
        $raiseError->ValidationError($product_detail_video->products_id != $products_id, ['product_detail_videos_id' => ['The product_details_id is not for the product']]);
        $orderDetailIds = OrderVideoDetail::where('product_details_videos_id', $product_detail_videos_id)->pluck('order_details_id');
        $orderIds = OrderDetail::whereIn('id', $orderDetailIds)
            //->where('products_id', $products_id)
            ->pluck('orders_id');
        $order = Order::where('users_id', $users_id)->whereIn('id', $orderIds)->where(function ($query) {
            $query->where('status', 'ok')->orWhere('status', 'manual_ok');
        })->orderBy('updated_at', 'desc')->first();


        //$order=Order::where('order_id', $order_id)->first();
        /*
        if($this->checkSessionInPackAndWholeProduct($product_detail_videos_id)){
           
            $order_detail = OrderDetail::where('products_id', $products_id)->where('orders_id', $order->id)->first();
            
            $found_refund = Refund::where('users_id', $users_id)->where('products_id', $products_id)->where('orders_id', $order->id)->first();
           
            $orderVideoDetail = OrderVideoDetail::where('order_details_id', $order_detail->id)->where('product_details_videos_id', $product_detail_videos_id)->first();
            $data=[
                'users_id' => $users_id,
                'saver_users_id' => Auth::user()->id,
                'products_id' => $products_id,
                'product_detail_videos_id' => $product_detail_videos_id,
                'order_details_id' => $order_detail->id,
                'order_video_details_id' => $orderVideoDetail ? $orderVideoDetail->id : null,
                'orders_id' => $order->id,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            //dd($data);
            if (!$found_refund) {
                Refund::create($data);
            }
            return (new OrderResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);

        }
       else if ($order ) {
        */
        //dd($order);
        $order_detail = OrderDetail::where('products_id', $products_id)->where('orders_id', $order->id)->first();
        if ($order_detail) {
            $orderVideoDetail = OrderVideoDetail::where('order_details_id', $order_detail->id)->where('product_details_videos_id', $product_detail_videos_id)->first();
            // OrderVideoDetail::where('order_details_id', $order_detail->id)->where('product_details_videos_id', $product_detail_videos_id)->delete();
            // $order_video_detail = OrderVideoDetail::where('order_details_id', $order_detail->id)->first();
            // if($order_video_detail == null) {
            //     $order_detail->delete();
            // }
            $otherOrderIds = OrderDetail::where('products_id', $products_id)
                ->where('users_id', $users_id)
                ->where('orders_id', '!=', $order->id)
                ->where('all_videos_buy', 1)
                ->pluck('orders_id');
            $otherOrdersCount = Order::whereIn('id', $otherOrderIds)
                ->whereIn('status', ['ok', 'manual_ok'])
                ->count();
            $refunds = Refund::whereIn('orders_id', $otherOrderIds)->where('is_deleted', 0)->count();
            if ($otherOrdersCount - $refunds === 0) {
                UserProduct::where('users_id', $users_id)->where('products_id', $products_id)->where('partial', 1)->delete();
                UserVideoSession::where('users_id', $users_id)->where('video_sessions_id', $product_detail_video->video_sessions_id)->delete();
            }
            $found_refund = Refund::where('users_id', $users_id)->where('products_id', $products_id)->where('orders_id', $order->id)->first();
            if (!$found_refund) {
                Refund::create([
                    'users_id' => $users_id,
                    'saver_users_id' => Auth::user()->id,
                    'products_id' => $products_id,
                    'product_detail_videos_id' => $product_detail_videos_id,
                    'order_details_id' => $order_detail->id,
                    'order_video_details_id' => $orderVideoDetail ? $orderVideoDetail->id : null,
                    'orders_id' => $order->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
        }
        return (new OrderResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
        // }
        return (new OrderResource(null))->additional([
            'errors' => ['not_found' => ['order not found']],
        ])->response()->setStatusCode(404);
    }

    public function checkSessionInPackAndWholeProduct($product_detail_videos_id)
    {
        $product_detail_videos = ProductDetailVideo::where('id', $product_detail_videos_id)->first();
        $user_product = UserProduct::where('products_id', $product_detail_videos->products_id)->where('partial', 0)->first();
        return $user_product;
    }
    public function updateVideoDetailChairPrice($order_detail_id, $flag = false)
    {

        $total_price = OrderChairDetail::where('order_details_id', '=', $order_detail_id)
            ->sum('price');
        //->get();

        $order_detail = OrderDetail::where('id', $order_detail_id)
            ->first();

        if ($order_detail !== null) {
            $order_detail["price"] = $total_price;
            $order_detail["total_price_with_coupon"] = $total_price;
            $order_detail["total_price"] = $total_price;
            $order_detail->save();
        }
    }
    // public function getDetails($user_id,$before,$after)
    // {
    //    $audit=new AdminLog;
    //    $user=User::whereId($user_id)->first();
    //    
    //    $user_fullName=$user->first_name . " " . $user->last_name;
    //   
    //    $log_result=AdminLog::deleteRecord($user->id,$user_fullName,$before,$after);
    //     return $log_result;

    // }
    public function storeProductPackage(storeProductPackageRequest $request)
    {
        $user = Auth::user();
        $utilObject = new buyProductsAccordingUserMobile;

        $product_before_buyed = $this->checkUserProduct($request->user_id, $request->product_id, 0);
        if ($product_before_buyed) {
            return (new OrderResource(null))->additional([
                'errors' => ["product" => "this product buyed before!"],
            ])->response()->setStatusCode(403);
        }
        $order = $utilObject->AddToOrder($request->user_id, $request->product_id, 1, "خرید محصول  توسط مدیر " . $user->first_name . "  " . $user->last_name);
        $orderDetails = $utilObject->orderDetails($request->child_product_ids, $request->product_id, $order->id);


        $productPartialBuyed = $this->checkUserProduct($request->user_id, $request->product_id, 1);
        if ($productPartialBuyed) {
            $productPartialBuyed->delete(); /// delete if user buy one  session of a product
        }

        return (new OrderResource($order))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
}

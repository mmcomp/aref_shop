<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddCouponToTheCartRequest;
use App\Http\Requests\User\AddProductToCartRequest;
use App\Http\Requests\User\DeleteProductFromCartRequest;
use App\Http\Requests\User\DeleteMicroProductFromCartRequest;
use App\Http\Requests\User\AddMicroProductToCartRequest;
use App\Http\Requests\User\DeleteCouponFromCartRequest;
use App\Http\Resources\User\OrderResource;
use App\Models\Order;
use App\Models\Temp;
use App\Models\OrderDetail;
use App\Models\OrderVideoDetail;
use App\Models\Product;
use App\Models\ProductDetailVideo;
use App\Models\UserProduct;
use App\Models\UserVideoSession;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\Payment;
use App\Utils\MellatPayment;
use App\Utils\RaiseError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Log;
use Exception;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\User\AddProductToCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddProductToCartRequest $request)
    {

        $user_id = Auth::user()->id;
        $number = $request->input('number', 1);
        $products_id = $request->input('products_id');
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        if (!$order) {
            $order = Order::create([
                'users_id' => $user_id,
                'status' => 'waiting',
            ]);
        }
        $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        if ($orderDetail && $product->type == 'normal') {
            $orderDetail->number += $number;
            $orderDetail->total_price = $orderDetail->number * $orderDetail->price;
            $orderDetail->total_price_with_coupon = $orderDetail->total_price;
            $orderDetail->save();
        } else if ($orderDetail && $product->type == 'video' && !$orderDetail->all_videos_buy) {
            $orderDetail->all_videos_buy = 1;
            OrderVideoDetail::where('order_details_id', $orderDetail->id)->delete();
            $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
            $orderDetail->save();
        } else if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $user_id,
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
            'error' => null,
        ])->response()->setStatusCode(201);
    }
    /**
     * add a microProduct to the cart
     *
     * @param  \App\Http\Requests\User\AddMicroProductToCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function StoreMicroProduct(AddMicroProductToCartRequest $request)
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $products_id = $request->input('products_id');
        $product_details_id = $request->input('product_details_id');
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        if (!$order) {
            $order = Order::create([
                'users_id' => $user_id,
                'status' => 'waiting'
            ]);
        }
        $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $user_id,
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
        }
        $sumOfOrderDetailPrices = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->amount = $sumOfOrderDetailPrices;
        $order->save();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWholeCart()
    {

        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', '=', 'waiting')->with('orderDetails')->first();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * add coupon to the cart
     *
     * @param  \App\Http\Requests\User\AddCouponToTheCartRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function addCouponToTheCart(AddCouponToTheCartRequest $request)
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $coupon = Coupon::where('is_deleted', false)->where('name', $request->input('coupons_name'))->first();
        $products_id = $coupon->products_id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $raiseError->ValidationError($order == null, ['orders_id' => ['You don\'t have any waiting orders yet!']]);
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        $raiseError->ValidationError($orderDetail == null, ['products_id' => ['You don\'t have any orders for the product that you have coupon for...']]);
        $user_coupon = UserCoupon::where('users_id', $user_id)->where('coupons_id', $coupon->id)->first();
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
                    'users_id' => $user_id,
                    'coupons_id' => $coupon->id
                ]);
                return (new OrderResource($order))->additional([
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info("fails in addCouponToTheCart in User/CartController" . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new OrderResource(null))->additional([
                        'error' => "fails in addCouponToTheCart in User/CartController" . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new OrderResource(null))->additional([
                        'error' => "fails in addCouponToTheCart in User/CartController",
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new OrderResource(null))->additional([
            "errors" => ["all_videos_buy" => ["Coupon can not be used when you didn't buy all of a product!"]]
        ])->response()->setStatusCode(406);
    }
    public function deleteCouponFromCart(DeleteCouponFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $coupon = Coupon::where('is_deleted', false)->where('name', $request->input('coupons_name'))->first();
        $products_id = $coupon->products_id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $raiseError->ValidationError($order == null, ['orders_id' => ['You don\'t have any waiting orders yet!']]);
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        $raiseError->ValidationError($orderDetail == null, ['products_id' => ['You don\'t have any orders for the product that you have coupon for...']]);
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
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info("fails in deleteCouponToTheCart in User/CartController" . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new OrderResource(null))->additional([
                        'error' => "fails in deleteCouponToTheCart in User/CartController" . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new OrderResource(null))->additional([
                        'error' => "fails in deleteCouponToTheCart in User/CartController",
                    ])->response()->setStatusCode(500);
                }
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * destroy whole cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyWholeCart()
    {

        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $order->status = 'cancel';
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $item) {
                $item->where('orders_id', $order->id)->delete();
                OrderVideoDetail::where('order_details_id', $item->id)->delete();
            }
        }
        try {
            $order->save();
            return (new OrderResource($order))->additional([
                'error' => null,
            ])->response()->setStatusCode(204);
        } catch (Exception $e) {
            Log::info('failed in User/CartController/destoryWholeCart', json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new OrderResource(null))->additional([
                    'error' => 'destroying Whole Cart failed!' . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new OrderResource(null))->additional([
                    'error' => 'destroying Whole Cart failed!',
                ])->response()->setStatusCode(500);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\User\DeleteProductFromCartRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, DeleteProductFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $orderDetail = OrderDetail::where('id', $id)->first();
        $order = Order::where('users_id', $user_id)->where('status', '!=', 'cancel')->with('orderDetails')->first();
        $raiseError->ValidationError($orderDetail->order->user->id != $user_id, ['users_id' => ['This is order of another user!']]);
        OrderDetail::where('id', $id)->delete();
        if ($orderDetail->product->type == 'video') {
            if (!$orderDetail->all_videos_buy) {
                OrderVideoDetail::where('order_details_id', $id)->delete();
            }
        }
        $order = Order::where('id', $orderDetail->orders_id)->first();
        $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->save();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\User\DeleteProductFromCartRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMicroProduct($id, DeleteMicroProductFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $product_details_id = $request->input('product_details_id');
        $orderDetail = OrderDetail::find($id);
        $order = Order::where('users_id', $user_id)->where('status', '!=', 'cancel')->with('orderDetails')->first();
        $raiseError->ValidationError($orderDetail->order->user->id != $user_id, ['users_id' => ['This is order of another user!']]);
        $raiseError->ValidationError($orderDetail->product->type == 'video' && $orderDetail->all_videos_buy, ['all_videos_buy' => ['You have already bought ' . $orderDetail->product->name . ' therefore you can not remove a subproduct of it']]);
        if ($orderDetail->product->type == 'video' && !$orderDetail->all_videos_buy) {
            $order_video_details = OrderVideoDetail::where('order_details_id', $id)->pluck('product_details_videos_id')->toArray();
            $raiseError->ValidationError(!in_array($product_details_id, $order_video_details), ['product_details_id' => ['The product_details_id is not valid!']]);
            OrderVideoDetail::where('order_details_id', $id)->where('product_details_videos_id', $product_details_id)->delete();
            $found = OrderVideoDetail::where('order_details_id', $id)->count();
            if (!$found) {
                OrderDetail::where('id', $id)->delete();
                $order = Order::where('users_id', $user_id)->where('status', '!=', 'cancel')->with('orderDetails')->first();
            }
        }
        $order->amount = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->save();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * insert into user_video_sessions and user_products when buying is completed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeInsertAfterBuying($order)
    {

        $user = 0;
        $product = null;
        $data = [];
        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->products_id;
            $user = $order->users_id;
            $found_user_product = UserProduct::where('users_id', $user)->where('products_id', $product)->first();
            if (!$found_user_product) {
                $orderDetail->product->type == 'video' ? UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => !$orderDetail->all_videos_buy]) : UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => 0]);
            }
            if ($orderDetail->product->type == 'video') {
                if ($orderDetail->all_videos_buy) {
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $product)->pluck('video_sessions_id')->toArray();
                } else {
                    if ($orderDetail->orderVideoDetails) {
                        foreach ($orderDetail->orderVideoDetails as $orderVideoDetail) {
                            $videoSessionIds[] = $orderVideoDetail->productDetailVideo->video_sessions_id;
                        }
                    }
                }
                foreach ($videoSessionIds as $videoSessionId) {
                    $found_user_video_session = UserVideoSession::where('video_sessions_id', $videoSessionId)->where('users_id', $user)->first();
                    if (!$found_user_video_session) {
                        $data[] = [
                            "video_sessions_id" => $videoSessionId,
                            "users_id" => $user
                        ];
                    }
                }
            }
        }
        UserVideoSession::insert($data);
    }
    /**
     * complete buying
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeBuying()
    {

        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        if ($order) {
            if (!$order->amount) {
                $order->status = "ok";
                $order->save();
                $this->completeInsertAfterBuying($order);
                return (new OrderResource($order))->additional([
                    'error' => null,
                ])->response()->setStatusCode(201);
            } else {
                return MellatPayment::pay($order);
            }
        }
    }
    /**
     * return from mellat bank
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mellatBank(Request $request)
    {

        Temp::create([
            'output' => json_encode($request->all()),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        $sw = 0;
        $SaleOrderId = $request->input('SaleOrderId');
        $ResCode = $request->input('ResCode');
        $RefId = $request->input('RefId');
        $payment = Payment::where('is_deleted', false)->where('bank_orders_id', $SaleOrderId)->first();
        if ($payment) {
            $order = Order::where('status', 'processing')->find($payment->orders_id);
            if ($order) {
                $payment->bank_returned = json_encode($request->all());
                $payment->res_code = $ResCode;
                $payment->ref_id = $RefId;
                if ($ResCode) {
                    $payment->status = "error";
                    $order->status = "waiting";
                } else {
                    $payment->sale_reference_id = $request->input("SaleReferenceId");
                    $payment->sale_order_id = $request->input('SaleOrderId');
                    $payment->save();
                    $verify_output = MellatPayment::verify($order, $payment);
                    $verify_error = $verify_output["errors"];
                    if ($verify_error != null) {
                        $payment->status = "verify_error";
                        $order->status = "waiting";
                    } else {
                        $FinalAmount = $request->input('FinalAmount');
                        if ($FinalAmount != $order->amount * 10) {
                            $payment->status = "amount_error";
                            $order->status = "waiting";
                        }
                        $settle_output = MellatPayment::settle($order, $payment);
                        $settle_error = $settle_output["errors"];
                        if ($settle_error != null) {
                            $payment->status = "settle_error";
                            $order->status = "waiting";
                        } else {
                            $sw = 1;
                            $payment->status = "success";
                            $order->status = "ok";
                            $this->completeInsertAfterBuying($order);  
                        }
                    }
                }
                $payment->save();
                $order->save();
                return redirect(env('APP_URL') . env('BANK_REDIRECT_URL').'/'.$order->id.'/'.$sw);
            }
            Log::info('order not exists');
            return redirect(env('APP_URL') . env('BANK_REDIRECT_URL'));
        }
        Log::info('payment not exists');
        return redirect(env('APP_URL') . env('BANK_REDIRECT_URL'));
    }
    

}

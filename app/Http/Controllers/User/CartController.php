<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddCouponToTheCartRequest;
use App\Http\Requests\User\AddProductToCartRequest;
use App\Http\Requests\User\DeleteProductFromCartRequest;
use App\Http\Requests\User\DeleteMicroProductFromCartRequest;
use App\Http\Requests\User\AddMicroProductToCartRequest;
use App\Http\Requests\User\DeleteCouponFromCartRequest;
use App\Http\Requests\User\StoreProductPackageRequest;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\User\OrderPackageDetailResource;

use App\Models\Order;
use App\Models\Temp;
use App\Models\OrderDetail;
use App\Models\OrderVideoDetail;
use App\Models\OrderChairDetail;
use App\Models\OrderPackageDetail;
use App\Models\Product;
use App\Models\VideoSession;
use App\Models\ProductDetailVideo;
use App\Models\UserProduct;
use App\Models\UserVideoSession;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\ProductDetailPackage;
use App\Models\Payment;
use App\Models\ProductDetailChair;
use App\Models\User;
use App\Imports\BuyProductForRowsImport;
use App\Utils\Buying;
use App\Utils\MellatPayment;
use App\Utils\RaiseError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Log;
use Exception;

class CartController extends Controller
{
    public function __construct(private Buying $buying)
    {

    }

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
        $order = $this->addToOrder($request);
        return (new OrderResource($order))->additional([
            'errors' => null,
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
                'status' => 'waiting',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
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
                            "chair_number" => $chair,
                        ],
                        [
                            "order_details_id" => $orderDetail->id,
                            "chair_number" => $chair,
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWholeCart()
    {
        $now = Carbon::now();
        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', '=', 'waiting')->first();
        if ($order) {
            $startDate = Carbon::parse($now->format("Y-m-d"));
            $endDate = Carbon::parse($order->updated_at->format("Y-m-d"));
            $diffInDays = $startDate->diffInDays($endDate);
            if ($diffInDays > 2) {
                $orderDetailIds = OrderDetail::where('orders_id', $order->id)->pluck('id');
                OrderChairDetail::whereIn('order_details_id', $orderDetailIds)->delete();
                OrderVideoDetail::whereIn('order_details_id', $orderDetailIds)->delete();
                OrderPackageDetail::whereIn('order_details_id', $orderDetailIds)->delete();
                OrderDetail::whereIn('id', $orderDetailIds)->delete();
                Order::where('id', $order->id)->delete();
            } else {
                $order = Order::where('users_id', $user_id)->where('status', '=', 'waiting')->with('orderDetails.orderChairDetails')->first();

            }
        }


        return (new OrderResource($order))->additional([
            'errors' => null,
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
        $user = Auth::user();
        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $coupon = Coupon::where('is_deleted', false)->where('name', $request->input('coupons_name'))->first();
        $product = Product::find($coupon->products_id);
        $products_id = $coupon->products_id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $raiseError->ValidationError($order == null, ['orders_id' => ['You don\'t have any waiting orders yet!']]);
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        $raiseError->ValidationError($orderDetail == null, ['products_id' => ['You don\'t have any orders for the product that you have coupon for']]);
        $another_user_used_coupon = UserCoupon::where('coupons_id', $coupon->id)
            ->first();
        $user_coupon = UserCoupon::where('users_id', $user_id)
            ->where('coupons_id', $coupon->id)
            ->first();
        if ($another_user_used_coupon) {
            return (new OrderResource(null))->additional([
                'errors' => ["already used" => ["The discount code has already been used."]],
            ])->response()->setStatusCode(406);
        }
        if ($user_coupon) {
            return (new OrderResource(null))->additional([
                'errors' => ["already applied" => ["The discount code has already been applied."]],
            ])->response()->setStatusCode(406);
        } else if ($coupon->expired_at != null && $coupon->expired_at < Carbon::now()->format('Y-m-d')) {
            return (new OrderResource(null))->additional([
                'errors' => ["expired" => ["The discount code has been expired"]],
            ])->response()->setStatusCode(406);
        }
        if (($orderDetail->all_videos_buy && $product->type === "video") || ($product->type !== "video")) {

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

                if ($coupon->school_id && !$user->school_id) {
                    $user->school_id = $coupon->school_id;
                    $user->save();
                }

                return (new OrderResource($order))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                //Log::info("fails in addCouponToTheCart in User/CartController" . json_encode($e));
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
            "errors" => ["all_videos_buy_1" => ["Coupon can not be used when you didn't buy all of a product!"]]
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
                Log::info("fails in deleteCouponToTheCart in User/CartController" . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in deleteCouponToTheCart in User/CartController" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new OrderResource(null))->additional([
                        'errors' => ["fail" => ["fails in deleteCouponToTheCart in User/CartController"]],
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
                OrderPackageDetail::where('order_details_id', $item->id)->delete();
            }
        }
        try {
            $order->save();
            return (new OrderResource($order))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        } catch (Exception $e) {
            Log::info('failed in User/CartController/destoryWholeCart', json_encode($e));
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

        if ($orderDetail->product->type === 'package') {
            OrderPackageDetail::where('order_details_id', $id)->delete();
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
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    public function destroyChairMicroProduct($id)
    {
        $user_id = Auth::user()->id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $orderChairDetail = OrderChairDetail::whereId($id)->first();
        if ($orderChairDetail !== null) {
            $orderDetailId = $orderChairDetail->order_details_id;

            //$chair_price=$orderChairDetail->price;
            if ($orderDetailId !== null) {
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
                    // $order->save();
                } else {
                    $order->amount = 0;
                    //$order->save();
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeBuying()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        $order->school_id = $user->school_id;
        $order->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $order->save();
        if ($order) {
            $validation = $this->validateOrderChairs($order);
            if (!$validation) {
                return response([
                    'errors' => 'some chairs are taken',
                ])->setStatusCode(406);
            }
            if (!$order->amount) {
                $order->status = "ok";
                $order->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $order->save();
                $this->buying->completeInsertAfterBuying($order);
                return (new OrderResource($order))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } else {
                return MellatPayment::pay($order);
            }
        }

        return response([
            'errors' => 'order not found',
        ])->setStatusCode(404);
    }

    public function validateOrderChairs($order)
    {
        $orderDetails = $order->orderDetails()->with('product')->with('orderChairDetails')->get()->where('product.type', 'chairs');
        if (count($orderDetails) > 0) {
            ProductController::cleanProccessingOrders();
        }
        foreach ($orderDetails as $orderDetail) {
            $productId = $orderDetail->products_id;
            $chairs = [];
            foreach ($orderDetail->orderChairDetails as $orderChairDetails) {
                $chairs[] = $orderChairDetails->chair_number;
            }
            if (count($chairs) > 0) {
                $reserved = ProductController::_GetListOfReservedChairs($productId)->toArray();

                if (count(array_intersect($chairs, $reserved)) > 0) {
                    return false;
                }
            }
        }
        return true;
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
                            $order->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                            $this->buying->completeInsertAfterBuying($order);
                        }
                    }
                }
                $payment->save();
                $order->save();
                return redirect(env('APP_URL') . env('BANK_REDIRECT_URL') . '/' . $order->id . '/' . $sw);
            }
            Log::info('order not exists');
            return redirect(env('APP_URL') . env('BANK_REDIRECT_URL'));
        }
        Log::info('payment not exists');
        return redirect(env('APP_URL') . env('BANK_REDIRECT_URL'));
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
    // public function minusChairPrice($order_detail_id,$chair_price)
    // {
    //     $order_detail= OrderDetail::where('id',$order_detail_id)
    //             ->first();
    //
    //             if($order_detail!==null)
    //             {
    //                 $order_detail["price"]=$order_detail["price"] - $chair_price;
    //                 $order_detail["total_price_with_coupon"]=$order_detail["total_price_with_coupon"] - $chair_price;
    //                 $order_detail["total_price"]=$order_detail["total_price"] - $chair_price;
    //                 $order_detail->save();
    //                 return true;
    //             }
    //             return false;
    // }
    public function storeProductPackage(StoreProductPackageRequest $request)
    {
        $user_id = Auth::user()->id;
        $data = $this->addToOrder($request);
        $order_id = $data->id;
        $order_Detail_id = OrderDetail::where("products_id", $request->products_id)->where("orders_id", $order_id)->first();
        if ($order_Detail_id) {
            foreach ($request->child_product_ids as $child_product_id) {
                $data = [
                    "order_details_id" => $order_Detail_id->id,
                    "product_child_id" => $child_product_id
                ];
                $orderDetailIds = OrderPackageDetail::where("order_details_id", $order_Detail_id->id)
                    ->where("product_child_id", $child_product_id)
                    ->get();
                if (Count($orderDetailIds) > 0) {
                    if (!$this->deleteAllOrderPackageDetails($orderDetailIds)) {
                        return response([
                            "errors" => "can not delete $child_product_id product",
                        ])->setStatusCode(406);
                    }
                }
                if (!OrderPackageDetail::create($data)) {
                    return (new OrderPackageDetailResource($data))->additional([
                        'errors' => ['OrderPackageDetail' => ['there is an error in data']],
                    ])->response()->setStatusCode(406);
                }
            }
            $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
            return (new OrderResource($order))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
            // return (new OrderPackageDetailResource(null));
        } else {
            return (new OrderPackageDetailResource(null))->additional([
                'errors' => ['order_Detail_id' => ['order_Detail_id is not exist']],
            ])->response()->setStatusCode(404);
        }
    }
    public function deleteAllOrderPackageDetails($orderDetailIds)
    {
        return OrderPackageDetail::find($orderDetailIds[0]->id)->delete();
    }

    /**
     * Buy a product for each row in an uploaded Excel file.
     * Expected columns: نام, نام خانوادگی, نام کاربری, کدملی, products_id
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyProductForRows(Request $request)
    {
        $rows = Excel::toArray(new BuyProductForRowsImport, $request->file('file'))[0];
        $results = ['success' => 0, 'failed' => []];

        // Skip the header row (index 0)
        foreach (array_slice($rows, 1) as $index => $row) {
            $nationalCode = $row[3] ?? null;
            $mobile = $row[2] ?? null;
            $productsId = $row[4] ?? null;

            if (!$mobile) {
                $results['failed'][] = ['row' => $index, 'reason' => 'missing mobile (نام کاربری)'];
                continue;
            }

            if (!$productsId) {
                $results['failed'][] = ['row' => $index, 'reason' => 'missing products_id'];
                continue;
            }

            $user = null;
            if ($nationalCode) {
                $user = User::where('national_code', $nationalCode)->first();
            }
            if (!$user) {
                $user = User::where('email', '0' . $mobile)->first();
            }

            if (!$user) {
                $user = new User(
                    [
                        "first_name" => $row[0],
                        "last_name" => $row[1],
                        "email" => '0' . $mobile,
                        "national_code" => $nationalCode,
                    ]
                );
            }

            $product = Product::where('is_deleted', false)->where('id', $productsId)->first();
            if (!$product) {
                $results['failed'][] = ['row' => $index, 'reason' => 'product not found'];
                continue;
            }

            $alreadyOwned = UserProduct::where('users_id', $user->id)
                ->where('products_id', $productsId)
                ->exists();
            if ($alreadyOwned) {
                $results['failed'][] = ['row' => $index, 'reason' => 'user already has this product'];
                continue;
            }

            try {
                DB::transaction(function () use ($user, $product, $productsId, &$results) {
                    $now = Carbon::now()->format('Y-m-d H:i:s');
                    $order = Order::create([
                        'users_id' => $user->id,
                        'status' => 'ok',
                        'amount' => $product->sale_price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    OrderDetail::create([
                        'orders_id' => $order->id,
                        'products_id' => $productsId,
                        'price' => $product->sale_price,
                        'users_id' => $user->id,
                        'all_videos_buy' => 1,
                        'number' => 1,
                        'total_price' => $product->sale_price,
                        'total_price_with_coupon' => $product->sale_price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $this->buying->completeInsertAfterBuying($order);
                    $results['success']++;
                });
            } catch (Exception $e) {
                $results['failed'][] = ['row' => $index, 'reason' => $e->getMessage()];
            }
        }

        return response()->json(['data' => $results, 'errors' => null])->setStatusCode(200);
    }

    public function addToOrder($request)
    {
        $user_id = Auth::user()->id;
        $school_id = Auth::user()->school_id;
        $number = intval($request->input('number', "1"));
        $products_id = $request->input('products_id');
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        if (!$order) {
            $order = Order::create([
                'school_id' => $school_id,
                'users_id' => $user_id,
                'status' => 'waiting',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
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
        return $order;
    }
}

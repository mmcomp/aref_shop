<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddProductToCartRequest;
use App\Http\Requests\User\DeleteProductFromCartRequest;
use App\Http\Requests\User\DeleteMicroProductFromCartRequest;
use App\Http\Requests\User\AddMicroProductToCartRequest;
use App\Http\Resources\User\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderVideoDetail;
use App\Models\Product;
use App\Models\ProductDetailVideo;
use App\Utils\RaiseError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $sum = 0;
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
        $orderDetailPricesArray = OrderDetail::where('orders_id', $order->id)->pluck('total_price_with_coupon')->toArray();
        foreach ($orderDetailPricesArray as $price) {
            $sum += $price;
        }
        $order->amount = $sum;
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
        $sumOfOrderDetailPrices = 0;
        $sumOfOrderVideoDetailPrices = 0;
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
                'price' => $product->type == 'normal' ? $product->sale_price : 0,
                'users_id' => $user_id,
                'number' => 1,
                'total_price' => DB::raw('number * price'),
                'total_price_with_coupon' => DB::raw('number * price')
            ]);
        } else if ($orderDetail && $orderDetail->all_videos_buy) {
            return (new OrderResource(null))->additional([
                'error' => 'already added!',
            ])->response()->setStatusCode(406);
        }
        if ($product->type == 'video') {
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('id', $product_details_id)->where('products_id', $products_id)->first();
            $raiseError->ValidationError($product_detail_video == null, ['product_detail_videos_id' => ['The product_details_id is not valid!']]);
            $found_order_video_detail = OrderVideoDetail::where('order_details_id', $orderDetail->id)->first();

            if (!$found_order_video_detail) {
                OrderVideoDetail::create([
                    'order_details_id' => $orderDetail->id,
                    'product_details_videos_id' => $product_details_id,
                    'price' => $product_detail_video->price,
                    'number' => 1
                ]);
            }
            $orderVideoDetailPricesArray = OrderVideoDetail::where('order_details_id', $orderDetail->id)->pluck('price')->toArray();
            foreach ($orderVideoDetailPricesArray as $price) {
                $sumOfOrderVideoDetailPrices += $price;
            }
            $orderDetail->total_price = $sumOfOrderVideoDetailPrices;
            $orderDetail->total_price_with_coupon = $sumOfOrderVideoDetailPrices;
            $orderDetail->price = $orderDetail->total_price_with_coupon;
            $orderDetail->save();
        }
        $orderDetailPricesArray = OrderDetail::where('orders_id', $order->id)->pluck('total_price_with_coupon')->toArray();
        foreach ($orderDetailPricesArray as $price) {
            $sumOfOrderDetailPrices += $price;
        }
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
        $order = Order::where('users_id', $user_id)->where('status', '!=', 'cancel')->first();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
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
        $order = Order::where('users_id', $user_id)->first();
        $order->status = 'cancel';
        try {
            $order->save();
            return (new OrderResource(null))->additional([
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
        $raiseError->ValidationError($orderDetail->order->user->id != $user_id, ['users_id' => ['This is order of another user!']]);
        OrderDetail::where('id', $id)->delete();
        if ($orderDetail->product->type == 'video') {
            if (!$orderDetail->all_videos_buy) {
                OrderVideoDetail::where('order_details_id', $id)->delete();
            }
        }
        return (new OrderResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(204);
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
        $raiseError->ValidationError($orderDetail->order->user->id != $user_id, ['users_id' => ['This is order of another user!']]);
        $raiseError->ValidationError($orderDetail->product->type == 'video' && $orderDetail->all_videos_buy, ['all_videos_buy' => ['You have already bought ' . $orderDetail->product->name . ' therefore you can not remove a subproduct of it']]);
        if ($orderDetail->product->type == 'video' && !$orderDetail->all_videos_buy) {
            OrderVideoDetail::where('order_details_id', $id)->where('product_details_videos_id', $product_details_id)->delete();
            $found = OrderVideoDetail::where('order_details_id', $id)->count();
            if (!$found) OrderDetail::where('id', $id)->delete();
        }
        return (new OrderResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(204);
    }
}

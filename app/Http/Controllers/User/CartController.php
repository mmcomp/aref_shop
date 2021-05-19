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

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->where('users_id', $user_id)->first();
        if ($orderDetail && $product->type == 'normal') {
            $orderDetail->number += $number;
            $orderDetail->save();
        } else if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->price,
                'users_id' => $user_id,
                'number' => $product->type != 'normal' ? 1 : $number
            ]);
        }
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
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->where('users_id', $user_id)->first();
        if(!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->price,
                'users_id' => $user_id
            ]);
        } else if($orderDetail && $orderDetail->all_videos_buy) {
            return (new OrderResource(null))->additional([
                'error' => 'already added!',
            ])->response()->setStatusCode(406);
        }
        if ($product->type == 'video') {
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('id', $product_details_id)->where('products_id', $products_id)->first();
            $raiseError->ValidationError($product_detail_video == null, ['product_detail_videos_id' => ['The product_details_id is not valid!']]);
            $found_order_video_detail = OrderVideoDetail::where('order_details_id', $orderDetail->id)->where('product_details_videos_id', $product_details_id)->where('price', $product->price)->first();
            if (!$found_order_video_detail) {
                OrderVideoDetail::create([
                    'order_details_id' => $orderDetail->id,
                    'product_details_videos_id' => $product_details_id,
                    'price' => $product_detail_video->price
                ]);
            }
        }
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\User\DeleteProductFromCartRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, DeleteProductFromCartRequest $request)
    {

        $user_id = Auth::user()->id;
        OrderDetail::where('id', $id)->where('users_id', $user_id)->delete();
        return (new OrderResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(204);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\User\DeleteProductFromCartRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMicroProduct($id, DeleteMicroProductFromCartRequest $request)
    {

        $raiseError = new RaiseError;
        $product_details_id = $request->input('product_details_id');
        $orderDetail = OrderDetail::find($id);
        $product = Product::where('is_deleted', false)->find($orderDetail->products_id);
        $raiseError->ValidationError($product == null , ['products_id' => ['The product does not exist']]);
        if($product->type == 'video') {
           OrderVideoDetail::where('order_details_id', $id)->where('product_details_videos_id', $product_details_id)->delete();        
        }
        return (new OrderResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(204);
    }
}

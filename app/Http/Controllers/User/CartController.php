<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddProductToCartRequest;
use App\Http\Resources\User\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
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

        $data = [];
        $user_id = Auth::user()->id;
        $order = Order::where('is_deleted', false)->where('users_id', $user_id)->where('status', 'waiting')->first();
        if (!$order) {
            $order = Order::create([
                'users_id' => $user_id,
                'status' => 'waiting',
            ]);
        }
        $product = Product::where('is_deleted', false)->where('id', $request->input('products_id'))->first();
        $orderDetail = OrderDetail::where('is_deleted', false)->where('orders_id', $order->id)->where('products_id', $request->input('products_id'))->first();
        if(!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $request->input('products_id'),
                'price' => $product->price,
                'users_id' => $user_id,
                'number' => $request->input('number'),
                'status' => 'waiting',
            ]);
        } 
        else {
            $orderDetail->number += $request->input('number');
            $orderDetail->save();
        }
        if ($product->type == 'video') {
            $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->pluck('video_sessions_id');
            $countOfUserVideoSessions = UserVideoSession::where('users_id', $user_id)->whereIn('video_sessions_id', $videoSessionIds)->count();
            if(!$countOfUserVideoSessions) {
                $i = 0;
                foreach ($videoSessionIds as $id) {
                    $data[$i]['users_id'] = $user_id;
                    $data[$i]['video_sessions_id'] = $id;
                    $i++;
                }
                UserVideoSession::insert($data);
                $orderDetail->all_videos_buy = 1;
                $orderDetail->save();
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddProductToCartRequest;
use App\Http\Resources\User\OrderResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;

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
        $foundOrder = Order::where('is_deleted', false)->where('users_id', $user_id)->where('status', 'waiting')->first();
        $order = Order::create([
            'users_id' => $user_id,
            'status' => 'waiting'
        ]);
        $product = Product::where('is_deleted', false)->where('id', $request->input('products_id'))->first(); 
        OrderDetail::create([
             'orders_id' => $order->id,
             'products_id' => $request->input('products_id'),
             'price' => $product->price,
             'users_id' => $user_id,
             'number' => $request->input('number'),
             'status' => 'waiting'
        ]);
        $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->pluck('video_sessions_id');
        $i = 0;
        foreach($videoSessionIds as $id) {
            $data[$i]['users_id'] = $user_id;
            $data[$i]['video_sessions_id'] = $id;
            $i++;
        }
        UserVideoSession::insert($data);
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

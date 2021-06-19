<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderCollection;
use App\Http\Resources\User\OrderResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

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

        $user_id = Auth::user()->id;
        $order = Order::find($id);
        if ($order != null) {
            if ($order->users_id != $user_id) {
                return (new OrderResource(null))->additional([
                    'errors' => ['auth_error' => ['This order does not belong to you!']],
                ])->response()->setStatusCode(406);
            }
            return (new OrderResource($order))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new OrderResource(null))->additional([
            'errors' => ['order' => ['Order does not exist!']],
        ])->response()->setStatusCode(406);
    }
    /**
     * show factors of authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showOrdersOfAuthUser()
    {

        $user_id = Auth::user()->id;
        $orders = Order::where('users_id', $user_id)->where('status', 'ok')->orderBy('id', 'desc')->get();
        return (new OrderCollection($orders))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * show specific order of authenticated user
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSpecificOrderOfAuthUser($id)
    {

        $user_id = Auth::user()->id;
        $order = Order::where('id', $id)->where('users_id', $user_id)->where('status', 'ok')->orderBy('id', 'desc')->first();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
}

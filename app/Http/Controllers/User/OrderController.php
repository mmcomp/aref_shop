<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        $order = Order::where('users_id', $user_id)->find($id);
        if($order != null) {
            return (new OrderResource($order))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new OrderResource(null))->additional([
            'errors' => ['order' => ['Order does not exist!']],
        ])->response()->setStatusCode(406);
    }
}

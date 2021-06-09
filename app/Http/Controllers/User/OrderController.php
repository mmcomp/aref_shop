<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderResource;
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
        
        $order = Order::find($id);
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

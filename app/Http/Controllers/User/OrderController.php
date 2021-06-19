<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderCollection;
use App\Http\Resources\User\OrderVideoDetailCollection;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\User\OrderVideoDetailResource;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderVideoDetail;
use Illuminate\Support\Facades\Log;

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
     * show single sessions of authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleSessionsOfAuthUser()
    {

        $user_id = Auth::user()->id;
        $orderVideoDetailsArr = [];
        $orderVideoDetails = OrderVideoDetail::get();
        foreach($orderVideoDetails as $orderVideoDetail) {
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($orderVideoDetail->product_details_videos_id);
            if ($orderVideoDetail->orderDetail->order->status == 'ok' && $orderVideoDetail->orderDetail->order->users_id == $user_id && !$orderVideoDetail->orderDetail->all_videos_buy && $orderVideoDetail->orderDetail->product->type == 'video') {
                $found_user_videoSession = UserVideoSession::where('users_id', $user_id)->where('video_sessions_id', $product_detail_video->video_sessions_id)->first();
                $price = $product_detail_video->price != null ? $product_detail_video->price : $product_detail_video->videoSession->price;
                $checkPriceAndUserVideoSession = (!$price || $found_user_videoSession);
                $orderVideoDetailResource = (new OrderVideoDetailResource($orderVideoDetail))->check($checkPriceAndUserVideoSession);
                $orderVideoDetailsArr[] = $orderVideoDetailResource;
            }
        }
        return ((new OrderVideoDetailCollection($orderVideoDetailsArr)))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);

    }
}

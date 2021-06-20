<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderCollection;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\User\VideoSessionsResourceForShowingToStudentsResource;
use App\Http\Resources\User\VideoSessionsResourceForShowingToStudentsCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\UserVideoSession;

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
     * show student sessions from now to a week later
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showStudentSessions()
    {
        
        $user_id = Auth::user()->id;
        $video_sessions_arr = [];
        $date = date("Y-m-d");
        $to_date = date("Y-m-d", strtotime("+7 day", strtotime($date)));
        $user_video_sessions = UserVideoSession::where('users_id', $user_id)->whereHas('videoSession', function($query) use ($date, $to_date) {
            $query->where('start_date', '>=', $date)->where('start_date', '<=', $to_date);
        })->get();

        foreach($user_video_sessions as $user_video_session) {
            $video_sessions_arr[] = $user_video_session->videoSession;
        }
        return (new VideoSessionsResourceForShowingToStudentsCollection($video_sessions_arr))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);

    }
    
     * show specific order of authenticated user
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSpecificOrderOfAuthUser($id)
    {

        $user_id = Auth::user()->id;
        $order = Order::where('id', $id)->where('users_id', $user_id)->where('status', 'ok')->first();
        return (new OrderResource($order))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
}

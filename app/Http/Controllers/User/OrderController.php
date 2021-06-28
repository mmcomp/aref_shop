<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderCollection;
use App\Http\Resources\User\OrderDetailCollection;
use App\Http\Resources\User\OrderResource;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use App\Models\Order;
use App\Models\OrderVideoDetail;
use App\Models\UserProduct;
use App\Models\Product;
use App\Http\Resources\User\VideoSessionsResourceForShowingToStudentsCollection;
use App\Http\Resources\User\OrderVideoDetailsForSingleSessionsResource;
use App\Http\Resources\User\OrderVideoDetailsForSingleSessionsCollection;
use App\Http\Resources\User\ProductForSingleSessionsCollection;
use Illuminate\Support\Facades\Auth;

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
        $orderVideoDetails = OrderVideoDetail::whereHas('orderDetail', function ($query) {
            $query->where('all_videos_buy', 0);
        })->whereHas('orderDetail.order', function ($query) use ($user_id) {
            $query->where('status', 'ok')->where('users_id', $user_id);
        })->whereHas('orderDetail.product', function ($query) {
            $query->where('type', 'video');
        })->get();

        foreach ($orderVideoDetails as $orderVideoDetail) {
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($orderVideoDetail->product_details_videos_id);
            $found_user_videoSession = UserVideoSession::where('users_id', $user_id)->where('video_sessions_id', $product_detail_video->video_sessions_id)->first();
            $price = $product_detail_video->price != null ? $product_detail_video->price : $product_detail_video->videoSession->price;
            $checkPriceAndUserVideoSession = (!$price || $found_user_videoSession);
            $orderVideoDetailResource = (new OrderVideoDetailsForSingleSessionsResource($orderVideoDetail))->check($checkPriceAndUserVideoSession);
            $orderVideoDetailsArr[] = $orderVideoDetailResource;
        }
        return ((new OrderVideoDetailsForSingleSessionsCollection($orderVideoDetailsArr)))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
     /**
     * complete courses of authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeCoursesOfAuthUser()
    {

        $user_id = Auth::user()->id;
        $user_video_products = UserProduct::where('users_id', $user_id)->where('partial', 0)->whereHas('product', function($query){
           $query->where('type', 'video');
        })->pluck('products_id')->toArray(); 
        $user_package_products = UserProduct::where('users_id', $user_id)->where('partial', 0)->whereHas('product', function($query){
            $query->where('type', 'package');
         })->get(); 
        $package_child_products = [];
        foreach($user_package_products as $package) {
           foreach($package->product->productDetailPackages as $productDetailPackage) {
               $package_child_products[] = $productDetailPackage->child_products_id;
           }   
        }
        $needed_product_ids = array_values(array_unique(array_merge($package_child_products, $user_video_products)));
        $products = Product::where('is_deleted', false)->whereIn('id', $needed_product_ids)->get();
        return (new ProductForSingleSessionsCollection($products))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /*
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
    
     /* show specific order of authenticated user
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

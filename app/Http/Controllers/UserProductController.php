<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\UserProduct;
use App\Models\Product;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;
use App\Models\User;
use App\Http\Requests\ReportSaleRequest;
use App\Http\Resources\ReportSaleOrderCollection;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\UserCollection;
use App\Utils\RaiseError;
use Log;

class UserProductController extends Controller
{

    /**
     * report sale for admin
     *
     * @param  \App\Http\Requests\ReportSaleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportSale(ReportSaleRequest $request)
    {
        $raiseError = new RaiseError;
        $mode = $request->input('mode');
        $users_id = $request->input('users_id');
        $products_id = $request->input('products_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($mode != "product") {
            $orders = null;
            if ($from_date) {
                $orders = Order::where('created_at', '>=', $from_date);
                Log::info("created_at >= $from_date");
            }
            if ($to_date) {
                if ($orders) {
                    $orders = $orders->where('created_at', '<=', $to_date);
                } else {
                    $orders = Order::where('created_at', '<=', $to_date);
                }
                Log::info("created_at <= $to_date");
            }
            if ($users_id) {
                if ($orders) {
                    $orders = $orders->where('users_id', $users_id);
                } else {
                    $orders = Order::where('users_id', $users_id);
                }
            }
            $orders = $orders->where(function ($query) {
                $query->where('status', 'ok')->orWhere('status', 'manual_ok');
            })->get();
            return (new ReportSaleOrderCollection($orders))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        } else {
            $product_details_id = $request->input('product_detail_videos_id');
            if ($product_details_id == null) {
                $user_products = UserProduct::where('products_id', $products_id)->where('partial', 0)->pluck('users_id');
                $users = User::whereIn('id', $user_products)->get();
                return (new UserCollection($users))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            }
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id', $products_id)->find($product_details_id);
            $raiseError->ValidationError($product_detail_video == null, ['product_detail_videos_id' => ['The product_details_id is not valid.']]);
            $user_video_sessions_id = UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id);
            if ($from_date) {
                $orders = $user_video_sessions_id->where('created_at', '>=', $from_date);
            }
            if ($to_date) {
                $orders = $user_video_sessions_id->where('created_at', '<=', $to_date);
            }
            $user_video_sessions_id = $user_video_sessions_id->pluck('users_id');
            $users = User::whereIn('id', $user_video_sessions_id)->get();
            return (new UserCollection($users))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
    }
}

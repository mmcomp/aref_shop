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
use App\Http\Resources\User\OrderDetailCollection;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\UserCollection;
use App\Models\OrderDetail;
use App\Utils\RaiseError;
use Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $school_id = null;
        if (Auth::user()->group->type == 'school-admin') {
            $school_id = Auth::user()->school_id;
        }
        $mode = $request->input('mode');
        $users_id = $request->input('users_id');
        $products_id = $request->input('products_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($mode != "product") {
            $orders = null;
            if ($from_date) {
                $orders = Order::whereRaw('date(updated_at) >= ?', [$from_date]);
            }
            if ($to_date) {
                if ($orders) {
                    $orders = $orders->whereRaw('date(updated_at) <= ?', [$to_date]);
                } else {
                    $orders = Order::whereRaw('date(updated_at) <= ?', [$to_date]);
                }
            }
            if ($users_id) {
                if ($orders) {
                    $orders = $orders->where('users_id', $users_id);
                } else {
                    $orders = Order::where('users_id', $users_id);
                }
            }
            if ($school_id) {
                // $orders = $orders->whereHas('user', function ($query) use ($school_id) {
                //     $query->where('school_id', $school_id);
                // });
                $orders->where('school_id', $school_id);
            }
            $orders = $orders->where(function ($query) {
                $query->where('status', 'ok')->orWhere('status', 'manual_ok');
            })
                ->orderBy("updated_at", "desc")
                ->get();
            return (new ReportSaleOrderCollection($orders))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        } else {
            $product_details_id = $request->input('product_detail_videos_id');
            $orderDetails = OrderDetail::where("products_id", $products_id)->whereDoesntHave("refund")->whereHas("order", function ($query) {
                $query->where("status", "ok")->orWhere('status', 'manual_ok');
            });
            if ($product_details_id != null) {
                $orderDetails = $orderDetails->where("all_videos_buy", false);
                $orderDetails = $orderDetails->whereHas("orderVideoDetails", function ($query) use ($product_details_id) {
                    $query->where("product_details_videos_id", $product_details_id);
                });
            }
            if ($school_id) {
                $orderDetails = $orderDetails->whereHas("user", function ($query) use ($school_id) {
                    $query->where('school_id', $school_id);
                });
            }
            $orderDetails = $orderDetails->orderBy("updated_at", "desc")/*->with("order.orderDetails")*/
                ->with("user")->get();
            return (new OrderDetailCollection($orderDetails))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
    }
}

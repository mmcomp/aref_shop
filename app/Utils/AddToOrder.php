<?php

namespace App\Utils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Audit;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\OrderVideoDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

 class AddToOrder
 {
    public function addToOrder(int $user_id, int $products_id, int $number=1,string $comment=null)
    {
         $user_id_creator = Auth::user()->id;
        // $number = 0;
        // if (isset($request->number))
        //     $number = $request->input('number', 1);
        // $products_id = $request->input('products_id');
        $products_id = $products_id;
        $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        if (!$order) {
            $order = Order::create([
                "saver_users_id" => $user_id_creator,
                'users_id' => $user_id,
                "comment" =>$comment, 
                'status' => 'waiting',
            ]);
        }
        $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        if ($orderDetail && $product->type == 'normal') {
            $orderDetail->number += $number;
            $orderDetail->total_price = $orderDetail->number * $orderDetail->price;
            $orderDetail->total_price_with_coupon = $orderDetail->total_price;
            $orderDetail->save();
        } else if ($orderDetail && $product->type == 'video' && !$orderDetail->all_videos_buy) {
            $orderDetail->all_videos_buy = 1;
            OrderVideoDetail::where('order_details_id', $orderDetail->id)->delete();
            $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
            $orderDetail->save();
        } else if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $user_id,
                'all_videos_buy' => 1,
                'number' => $product->type != 'normal' ? 1 : $number,
                'total_price' => DB::raw('number * price'),
                'total_price_with_coupon' => DB::raw('number * price')
            ]);
        }
        $orderDetailPricesArraySum = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        $order->amount = $orderDetailPricesArraySum;
        $order->save();
        return $order;
    }
 }





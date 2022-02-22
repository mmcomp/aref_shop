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
use Carbon\Carbon;
use UserProduct;

 class buyProductsAccordingUserMobile
 {
    public function addToOrder(int $user_id, int $products_id, int $number=1,string $comment=null)
    {      
        $user_id_creator = Auth::user()->id;
        $user=User::find($user_id);
        // $number = 0;
        // if (isset($request->number))
        //     $number = $request->input('number', 1);
        // $products_id = $request->input('products_id');
        if ($user->group->type == 'user') {
            $products_id = $products_id;
            $order = Order::where('users_id', $user_id)->where('status', 'manual_waiting')->first();
            if (!$order) {
                $order = Order::create([
                    "saver_users_id" => $user_id_creator,
                    'users_id' => $user_id,
                    "comment" =>$comment, 
                    'status' => 'manual_waiting',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
            return $order;
        }
        return null;
        // $product = Product::where('is_deleted', false)->where('id', $products_id)->first();
        // $orderDetail = OrderDetail::where('orders_id', $order->id)->where('products_id', $products_id)->first();
        // if ($orderDetail && $product->type == 'normal') {
        //     $orderDetail->number += $number;
        //     $orderDetail->total_price = $orderDetail->number * $orderDetail->price;
        //     $orderDetail->total_price_with_coupon = $orderDetail->total_price;
        //     $orderDetail->save();
        // } else if ($orderDetail && $product->type == 'video' && !$orderDetail->all_videos_buy) {
        //     $orderDetail->all_videos_buy = 1;
        //     OrderVideoDetail::where('order_details_id', $orderDetail->id)->delete();
        //     $order = Order::where('users_id', $user_id)->where('status', 'waiting')->first();
        //     $orderDetail->save();
        // } else if (!$orderDetail) {
        //     $orderDetail = OrderDetail::create([
        //         'orders_id' => $order->id,
        //         'products_id' => $products_id,
        //         'price' => $product->sale_price,
        //         'users_id' => $user_id,
        //         'all_videos_buy' => 1,
        //         'number' => $product->type != 'normal' ? 1 : $number,
        //         'total_price' => DB::raw('number * price'),
        //         'total_price_with_coupon' => DB::raw('number * price')
        //     ]);
        // }
        // $orderDetailPricesArraySum = OrderDetail::where('orders_id', $order->id)->sum('total_price_with_coupon');
        // $order->amount = $orderDetailPricesArraySum;
        // $order->save();
        // return $order;
    }
    public function orderDetails(int $products_id,int $orders_id, int $number=1)
    {      
        //$number = $request->input('number', 1);
        //$products_id = $request->input('products_id');
        $order = Order::find($orders_id);
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
            $order = Order::find($orders_id);
            $orderDetail->save();
        } else if (!$orderDetail) {
            $orderDetail = OrderDetail::create([
                'orders_id' => $order->id,
                'products_id' => $products_id,
                'price' => $product->sale_price,
                'users_id' => $order->users_id,
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
    public function completeBuying(int $orders_id,int $amount,string $description)
    {
        $order = Order::find($orders_id);
       // $amount = $request->input('amount');
        //$description = $request->input('description');
        $buying = new Buying;
        $order->status = "manual_ok";
        $order->amount = $amount;
        $order->comment = $description;
        $order->save();       
        $buying->completeInsertAfterBuying($order);
        return $order;
    }
 }





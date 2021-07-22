<?php

namespace App\Utils;

use App\Models\Order;
use App\Models\UserProduct;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;
use App\Models\ProductDetailPackage;
use App\Models\Product;

class Buying
{

    public function completeInsertAfterBuying(Order $order)
    {

        $user = 0;
        $product = null;
        $data = [];
        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->products_id;
            $user = $order->users_id;
            $found_user_product = UserProduct::where('users_id', $user)->where('products_id', $product)->first();
            if (!$found_user_product) {
                $orderDetail->product->type == 'video'
                    ?
                    UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => !$orderDetail->all_videos_buy])
                    :
                    UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => 0]);
            }
            if ($orderDetail->product->type == 'video') {
                if ($orderDetail->all_videos_buy) {
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $product)->pluck('video_sessions_id')->toArray();
                } else {
                    if ($orderDetail->orderVideoDetails) {
                        foreach ($orderDetail->orderVideoDetails as $orderVideoDetail) {
                            $videoSessionIds[] = $orderVideoDetail->productDetailVideo->video_sessions_id;
                        }
                    }
                }
                foreach ($videoSessionIds as $videoSessionId) {
                    $found_user_video_session = UserVideoSession::where('video_sessions_id', $videoSessionId)->where('users_id', $user)->first();
                    if (!$found_user_video_session) {
                        $data[] = [
                            "video_sessions_id" => $videoSessionId,
                            "users_id" => $user
                        ];
                    }
                }
            }
            if ($orderDetail->product->type == 'package') {
                $child_products = ProductDetailPackage::where('products_id', $orderDetail->product->id)->where('is_deleted', false)->pluck('child_products_id');
                $childData = [];
                $now = date("Y-m-d H:i:s");
                foreach ($child_products as $child_product) {
                    $childData[] = [
                        'users_id' => $user,
                        'products_id' => $child_product,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    $p = Product::where('is_deleted', false)->where('id', $child_product)->first();
                    if ($p->type == 'video') {
                        $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $p->id)->pluck('video_sessions_id')->toArray();
                        foreach ($videoSessionIds as $video_session_id) {
                            $data[] = [
                                'users_id' => $user,
                                'video_sessions_id' => $video_session_id,
                                'created_at' => $now,
                                'updated_at' => $now
                            ];
                        }
                    }
                }
                UserProduct::insert($childData);
                UserVideoSession::insert($data);
            }
        }
        UserVideoSession::insert($data);
    }
}

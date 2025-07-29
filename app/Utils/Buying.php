<?php

namespace App\Utils;

use App\Models\Order;
use App\Models\UserProduct;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;
use App\Models\ProductDetailPackage;
use App\Models\Product;
use App\Models\UserQuiz;
use Illuminate\Support\Facades\Log;

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
            if (!$found_user_product && ($orderDetail->product->type !== 'video')) {
                UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => 0]);
                // $orderDetail->product->type == 'video'
                //     ?
                //     UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => !$orderDetail->all_videos_buy])
                //     :
                //     UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => 0]);
            }
            if ($orderDetail->product->type == 'video') {
                if (!$found_user_product) {
                    UserProduct::create(['users_id' => $user, 'products_id' => $product, 'partial' => !$orderDetail->all_videos_buy]);
                } else if ($found_user_product && $found_user_product->partial === 1 && $orderDetail->all_videos_buy === 1) {
                    $found_user_product->partial = 0;
                    $found_user_product->update();
                }
                if ($orderDetail->all_videos_buy) {
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $product)->pluck('video_sessions_id')->toArray();
                } else {
                    if ($orderDetail->orderVideoDetails) {
                        foreach ($orderDetail->orderVideoDetails as $orderVideoDetail) {
                            $videoSessionIds[] = $orderVideoDetail->productDetailVideo->video_sessions_id;
                        }
                    }
                }
                $now = date("Y-m-d H:i:s");
                foreach ($videoSessionIds as $videoSessionId) {
                    $found_user_video_session = UserVideoSession::where('video_sessions_id', $videoSessionId)->where('users_id', $user)->first();
                    if (!$found_user_video_session) {
                        $data[] = [
                            "video_sessions_id" => $videoSessionId,
                            "users_id" => $user,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }
                Log::info("[completeInsertAfterBuying] orderDetail" . json_encode($orderDetail));
                Log::info("[completeInsertAfterBuying] videoSessionIds" . json_encode($videoSessionIds));
            }
            if ($orderDetail->product->type == 'package') {
                //$orderDetailPackage=$orderDetail->OrderPackageDetail;
                $child_products = $orderDetail->OrderPackageDetail->pluck('product_child_id');
                // $child_products = ProductDetailPackage::where('products_id', $orderDetail->product->id)->where('is_deleted', false)->pluck('child_products_id');

                $childData = [];
                $now = date("Y-m-d H:i:s");
                Log::info("[completeInsertAfterBuying] child_products" . json_encode($child_products));
                foreach ($child_products as $child_product) {
                    //$child_product_id = ProductDetailPackage::where("id", $child_product)->pluck("child_products_id");
                    $tmp = ProductDetailPackage::where("id", $child_product)->first();
                    if (!$tmp) {
                        continue;
                    }
                    $childData[] = [
                        'users_id' => $user,
                        'products_id' =>  $tmp->child_products_id, //$child_product,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];

                    $p = Product::where('is_deleted', false)->where('id', $tmp->child_products_id/* $child_product*/)->first();
                    if ($p->type == 'video') {
                        $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $p->id)->pluck('video_sessions_id')->toArray();

                        foreach ($videoSessionIds as $video_session_id) {
                            $found_user_video_session = UserVideoSession::where('users_id', $user)->where('video_sessions_id', $video_session_id)->first();

                            if (!$found_user_video_session) {
                                $data[] = [
                                    'users_id' => $user,
                                    'video_sessions_id' => $video_session_id,
                                    'created_at' => $now,
                                    'updated_at' => $now
                                ];
                            }
                        }
                    }
                }
                Log::info("[completeInsertAfterBuying] childData" . json_encode($childData));
                Log::info("[completeInsertAfterBuying] data" . json_encode($data));
                UserProduct::insert($childData);
                UserVideoSession::insert($data);
            }
            if ($orderDetail->product->type == 'quiz24') {
                $quizzes = $orderDetail->product->quizzes;
                foreach ($quizzes as $quiz) {
                    $userQuiz = UserQuiz::where('users_id', $user)->where('quizzes_id', $quiz->id)->first();
                    if (!$userQuiz) {
                        UserQuiz::create(['users_id' => $user, 'quizzes_id' => $quiz->id, 'created_at' => $now, 'updated_at' => $now]);
                    }
                }
            }
        }
        Log::info("[completeInsertAfterBuying] last_data" . json_encode($data));
        UserVideoSession::insert($data);
    }
}

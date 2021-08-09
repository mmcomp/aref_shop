<?php
//update previous buyers when a session is added to a product
namespace App\Utils;

use App\Models\Order;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use App\Models\ProductDetailPackage;
use App\Models\Product;
use Carbon\Carbon;
use Log;

class UpdatePreviousByers
{

    public function create($found, $request, $video_sessions_id = 0)
    {
        $sw = 0;
        $product_detail_video = null;
        if (!$found) {
            $product_detail_video = $request->input('video_sessions_id') == null ? ProductDetailVideo::create(array_merge($request->all(), ['video_sessions_id' => $video_sessions_id])):ProductDetailVideo::create($request->all());
            $completed_orders = Order::where('status', 'ok')->get();
            $data = [];
            foreach ($completed_orders as $order) {
                foreach ($order->orderDetails as $orderDetail) {
                    if ($orderDetail->product->id == $request->input('products_id') && $orderDetail->all_videos_buy && $orderDetail->product->type == 'video') {
                        $found_user_video_session = UserVideoSession::where('users_id', $order->users_id)->where('video_sessions_id', $request->input('video_sessions_id'))->first();
                        if (!$found_user_video_session) {
                            $data[] = [
                                'video_sessions_id' => $request->input('video_sessions_id') ? $request->input('video_sessions_id') : $video_sessions_id,
                                'users_id' => $order->users_id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ];
                        }
                    }
                    if ($orderDetail->product->id == $request->input('products_id') && $orderDetail->product->type == 'package') {
                        $child_products = ProductDetailPackage::where('products_id', $orderDetail->product->id)->pluck('child_products_id');
                        foreach ($child_products as $child_product) {
                            $p = Product::where('is_deleted', false)->where('id', $child_product)->first();
                            if ($p->type == 'video') {
                                $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $p)->pluck('video_sessions_id')->toArray();
                                foreach ($videoSessionIds as $video_session_id) {
                                    $found_user_video_session = UserVideoSession::where('users_id', $order->users_id)->where('video_sessions_id', $video_session_id)->first();
                                    if(!$found_user_video_session) {
                                        $data = [
                                            'users_id' => $order->users_id,
                                            'video_sessions_id' => $video_session_id
                                        ];
                                    }

                                }
                            }
                        }
                        UserVideoSession::insert($data);
                    }
                }
            }
            UserVideoSession::insert($data);
            $sw = 1;
        }
        if(!$sw) Log::info('fails in Utils/UpdatePreviousByers/create');
        return $product_detail_video;
    }

    public function update($request, $product_detail_video, $video_sessions_id = 0)
    {

        $sw = 0;
        $v_id = $request->input('video_sessions_id') == null ? $video_sessions_id : $request->input('video_sessions_id');
        $video_sessions_ids = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->pluck('video_sessions_id')->toArray();
        $raiseError = new RaiseError;
        if ($product_detail_video != null) {
            $raiseError->ValidationError(in_array($v_id, $video_sessions_ids), ['product_detail_video' => ['The product_detail_video is already recorded!']]);
            UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id)->delete();
            $product_detail_video->update($request->all());
            $sw = 1;
        }
        if(!$sw) Log::info('fails in Utils/UpdatePreviousByers/update');
    }
}

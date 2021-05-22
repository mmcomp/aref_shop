<?php
//update previous buyers when a session is added to a product
namespace App\Utils;

use App\Models\Order;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Carbon\Carbon;

class UpdatePreviousByers
{

    public function create($found, $request, $video_sessions_id = 0)
    {
        $sw = 0;
        $product_detail_video = null;
        if (!$found) {
            if($request->input('video_sessions_id') == null) {
                $product_detail_video = ProductDetailVideo::create(array_merge($request->all(), ['video_sessions_id' => $video_sessions_id]));
            } else {
                $product_detail_video = ProductDetailVideo::create($request->all());
            }
            $completed_orders = Order::where('status', 'ok')->get();
            $data = [];
            foreach ($completed_orders as $order) {
                foreach ($order->orderDetail as $orderDetail) {
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
                }
            }
            UserVideoSession::insert($data);
            $sw = 1;
        }
        return [$sw, $product_detail_video];
    }

    public function update($request, $product_detail_video, $video_sessions_id = 0)
    {

        $sw = 0;
        $v_id = $request->input('video_sessions_id') == null ? $video_sessions_id : $request->input('video_sessions_id');
        $found_product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->where('video_sessions_id', $request->input('video_sessions_id'))->first();
        $raiseError = new RaiseError;
        if ($product_detail_video != null) {
            $raiseError->ValidationError($found_product_detail_video, ['product_detail_video' => ['The product_detail_video is already recorded!']]);
            if (!$found_product_detail_video) {
                if ($product_detail_video->video_sessions_id != $v_id) {
                    UserVideoSession::where('video_sessions_id', $v_id)->delete();
                }
                $product_detail_video->update($request->all());
                $sw = 1;
            }
        }
        return $sw;
    }
}

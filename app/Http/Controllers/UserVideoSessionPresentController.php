<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVideoSessionPresentReportRequest;
use App\Http\Resources\UserVideoSessionPresentCollection;
use App\Models\UserVideoSessionPresent;
use App\Models\ProductDetailVideo;

class UserVideoSessionPresentController extends Controller
{

    public function report(UserVideoSessionPresentReportRequest $request)
    {

        $product_detail_videos_id = $request->input('product_detail_videos_id');
        $productDetailVideo = ProductDetailVideo::where('is_deleted', false)->find($product_detail_videos_id);
        $video_sessions_id = $productDetailVideo->video_sessions_id;
        $user_video_session_presrent = UserVideoSessionPresent::where('video_sessions_id', $video_sessions_id)->whereHas('user', function ($query) {
            $query->where('is_deleted',false);
        })->get();
        return (new UserVideoSessionPresentCollection($user_video_session_presrent))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVideoSessionReportRequest;
use App\Http\Resources\UserVideoSessionCollection;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;

class UserVideoSessionController extends Controller
{

    public function report(UserVideoSessionReportRequest $request)
    {

        $product_detail_videos_id = $request->input('product_detail_videos_id');
        $productDetailVideo = ProductDetailVideo::where('is_deleted', false)->find($product_detail_videos_id);
        $video_sessions_id = $productDetailVideo->video_sessions_id;
        $user_video_sessions = UserVideoSession::where('video_sessions_id', $video_sessions_id)->whereHas('user', function ($query) {
            $query->where('is_deleted',false);
        })->get();
        return (new UserVideoSessionCollection($user_video_sessions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}

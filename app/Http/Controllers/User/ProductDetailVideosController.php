<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\ProductDetailVideosResourceForShow;

class ProductDetailVideosController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video != null) {
            $found_user_videoSession = UserVideoSession::where('users_id', Auth::user()->id)->where('video_sessions_id', $product_detail_video->video_sessions_id)->first();
            $price = $product_detail_video->price != null ? $product_detail_video->price : $product_detail_video->videoSession->price;
            $checkPriceAndUserVideoSession = (!$price || $found_user_videoSession);
            return ((new ProductDetailVideosResourceForShow($product_detail_video))->check($checkPriceAndUserVideoSession))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailVideosResourceForShow(null))->additional([
            'errors' => ['productDetailVideo' => ['ProductDetailVideo not found!']],
        ])->response()->setStatusCode(404);
    }
}

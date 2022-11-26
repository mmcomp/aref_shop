<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Teacher\ProductDetailVideosResourceForShowForTeacher;
use App\Http\Resources\User\ProductDetailVideosResourceForConference;
use App\Utils\GetNameOfSessions;

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
        //dd(Auth::user()->id);
        $getNameOfSessions = new GetNameOfSessions;
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)
        ->where('id',$id)
        ->with('userVideoSession')
        ->with('userVideoSession.userVideoSessionHomework')
        ->get();
        return ($product_detail_video);
        // $product_detail_videos = [];
        // if ($product_detail_video != null) {
        //     $product_detail_videos = $getNameOfSessions->getProductDetailVideos($product_detail_video->product, Auth::user()->id);
        //     foreach ($product_detail_videos as $item) {
        //         if ($item->id == $product_detail_video->id) {
        //             $product_detail_video = $item;
        //         }
        //     }
        //     $found_user_videoSession = UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id)->get();
        //     $price = $product_detail_video->price != null ? $product_detail_video->price : ($product_detail_video->videoSession ?  $product_detail_video->videoSession->price : 0);
        //     //$checkPriceAndUserVideoSession = (!$price || $found_user_videoSession);
        //     return ((new ProductDetailVideosResourceForShowForTeacher($product_detail_video)))->additional([
        //         'errors' => null,
        //     ])->response()->setStatusCode(200);
        // }
        // return (new ProductDetailVideosResourceForShowForTeacher(null))->additional([
        //     'errors' => ['productDetailVideo' => ['ProductDetailVideo not found!']],
        // ])->response()->setStatusCode(404);
    }

    public function getOne($id){
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id',$id)->get();
        return ($product_detail_video);
    }

}   

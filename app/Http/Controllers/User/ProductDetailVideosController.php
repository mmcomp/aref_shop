<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Illuminate\Support\Facades\Auth;
use App\Utils\Number2Word;
use App\Http\Resources\User\ProductDetailVideosResourceForShow;
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

        //$number = new Number2Word;
        $getNameOfSessions = new GetNameOfSessions;
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        $product_detail_videos = [];
        if ($product_detail_video != null) {
            // $product = $product_detail_video->product;
            // $numArray = [];
            // $i = 1;
            // for ($indx = 0; $indx < count($product->productDetailVideos); $indx++) {
            //     $v = $product->productDetailVideos[$indx];
            //     $numArray[$v->id] = $v != null && $product->productDetailVideos[$indx]->extraordinary ? 0 : $i;
            //     $i = $numArray[$v->id] ? $i + 1 : $i;
            //     $product_detail_videos[] = $product->productDetailVideos[$indx];
            // }
            // for($j = 0; $j < count($product_detail_videos); $j++) {
            //     $persianAlphabetNum = $number->numberToWords($numArray[$product_detail_videos[$j]->id]);
            //     if($persianAlphabetNum != null) {
            //         $product_detail_videos[$j]->name = $product_detail_videos[$j]->name == null ? (strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م') : $product_detail_videos[$j]->name;
            //     }
            // }
            $product_detail_videos = $getNameOfSessions->getProductDetailVideos($product_detail_video->product);
            foreach($product_detail_videos as $item) {
               if($item->id == $product_detail_video->id) {
                   $product_detail_video = $item;
               }
            }
            $found_user_videoSession = UserVideoSession::where('users_id', Auth::user()->id)->where('video_sessions_id', $product_detail_video->video_sessions_id)->first();
            $price = $product_detail_video->price != null ? $product_detail_video->price : ($product_detail_video->videoSession ?  $product_detail_video->videoSession->price : 0);
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

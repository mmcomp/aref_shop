<?php
namespace App\Utils;
use App\Models\Product;
use App\Models\UserVideoSession;

class GetNameOfSessions {
    
    public function getProductDetailVideos(Product $product, int $users_id = 0)
    {

        $number = new Number2Word;
        $numArray = [];
        $i = 1;
        $product_detail_videos = [];
        $bouth_video_sessions = UserVideoSession::where('users_id', $users_id)
                                ->whereIn('video_sessions_id', $product->productDetailVideos->pluck('video_sessions_id'))
                                ->pluck('video_sessions_id')->toArray();
        for ($indx = 0; $indx < count($product->productDetailVideos); $indx++) {
            $v = $product->productDetailVideos[$indx];
            $numArray[$v->id] = $v != null && $product->productDetailVideos[$indx]->extraordinary ? 0 : $i;
            $i = $numArray[$v->id] ? $i + 1 : $i;
            $product_detail_videos[] = $product->productDetailVideos[$indx];
        }
        for($j = 0; $j < count($product_detail_videos); $j++) {
            $persianAlphabetNum = $number->numberToWords($numArray[$product_detail_videos[$j]->id]);
            if($persianAlphabetNum != null) {
                $product_detail_videos[$j]->name = $product_detail_videos[$j]->name == null ? (strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م') : $product_detail_videos[$j]->name;
            }
            $product_detail_videos[$j]->buyed_before = in_array($product_detail_videos[$j]->video_sessions_id, $bouth_video_sessions);
        }
        return $product_detail_videos;
    }

}

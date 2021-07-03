<?php
namespace App\Utils;
use App\Models\Product;

class GetNameOfSessions {
    
    public function getProductDetailVideos(Product $product)
    {

        $number = new Number2Word;
        $numArray = [];
        $i = 1;
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
        }
        return $product_detail_videos;
    }
}

<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\User\ProductDetailVideosForTodaySessionsResource;


class ProductDetailVideosForTodaySessionsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function (ProductDetailVideosForTodaySessionsResource $resource) use ($request) {
            $i = 1;
            $numArray = [];
            $product = $resource->product;
            for ($indx = 0; $indx < count($product->productDetailVideos); $indx++) {
                $v = $product->productDetailVideos[$indx];
                $numArray[$v->id] = $v != null && $product->productDetailVideos[$indx]->extraordinary ? 0 : $i;
                $i = $numArray[$v->id] ? $i + 1 : $i;
            }
            if (isset($numArray[$resource->id])) {
                $resource->numName = $numArray[$resource->id];
            }
            return $resource;
        })->filter()->all();
    }
}

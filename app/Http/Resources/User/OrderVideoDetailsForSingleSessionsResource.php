<?php

namespace App\Http\Resources\User;

//use App\Http\Resources\User\ProductDetailVideosResource;
use App\Http\Resources\User\ProductDetailVideosForSingleSessionsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\Number2Word;

class OrderVideoDetailsForSingleSessionsResource extends JsonResource
{
    protected $value;

    public function check($value)
    {
        $this->check = $value;
        return $this;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            $number = new Number2Word;
            $num = 0;
            $persianAlphabetNum = $number->numberToWords($this->numName); 
            if($this->productDetailVideo->name == null){
                $num = strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م';
            } else {
                $num = $this->productDetailVideo->name;
            }
            return [
                'id' => $this->id,
                'productDetailVideo' => (new ProductDetailVideosForSingleSessionsResource($this->productDetailVideo))->check($this->check),
                'price' => $this->price,
                'numName' => $num,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}

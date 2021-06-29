<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\Number2Word;


class ProductDetailVideosForTodaySessionsResource extends JsonResource
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
            if($this->name == null){
                $num = strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م';
            } else {
                $num = $this->name;
            }
            return [
                'id' => $this->id,
                'price' => $this->price,
                'product' => new ProductForSingleSessionsResource($this->product),
                'videoSession' => (new VideoSessionForTodaySessionsResource($this->videoSession))->checkToShowUrlOrNot($this->check),
                'numName' => $num
            ];
        }
    }
}

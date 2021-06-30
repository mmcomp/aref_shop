<?php

namespace App\Http\Resources\User;

use App\Utils\Number2Word;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailVideosForFreeSessionsResource extends JsonResource
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
            //dd($this->numName);
            if($this->numName != null) {
                $persianAlphabetNum = $number->numberToWords($this->numName); 
                if($this->name == null){
                    $num = strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م';
                } else {
                    $num = $this->name;
                }
            }
           
            return [
                'id' => $this->id,
                'product' => new ProductForSingleSessionsResource($this->product),
                'videoSession' => (new VideoSessionForSingleSessionsResource($this->videoSession))->checkToShowUrlOrNot($this->check),
                'numName' => $num
            ];
        }
    }
}

<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
            //$buyed_before = false;
            $persianAlphabetNum = $number->numberToWords($this->numName);
            if($this->name == null){
                $num = strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م';
            } else {
                $num = $this->name;
            }
            // if($this->videoSession->userVideoSession != null && $this->videoSession->userVideoSession->user->id == Auth::user()->id) {
            //     $buyed_before = true;
            // }
            return [
                'id' => $this->id,
                'buyed_before' => $this->buyed_before,
                'price' => $this->price,
                'product' => new ProductForSingleSessionsResource($this->product),
                'videoSession' => (new VideoSessionForTodaySessionsResource($this->videoSession))->checkToShowUrlOrNot($this->check),
                'numName' => $num
            ];
        }
    }
}

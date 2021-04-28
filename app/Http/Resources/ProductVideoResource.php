<?php

namespace App\Http\Resources;

use App\Utils\Number2Word;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVideoResource extends JsonResource
{
    protected $value;

    public function foo($value)
    {
        $this->foo = $value;
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
            if($this->videoSession){
                $numToWord = $this->foo[$this->videoSession->id] ? $number->numberToWords($this->foo[$this->videoSession->id]) : $this->product_detail_video_name;
                if (!$this->foo[$this->videoSession->id]) {
                    $num = $numToWord;
                } else {
                    $num = strpos($numToWord, "سه") !== false ? str_replace("سه", "سو", $numToWord) . 'م' : $numToWord . 'م';
                }
            }
            return [
                'id' => $this->id,
                'start_date' => $this->videoSession ? $this->videoSession->start_date : null,
                'start_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->start_time)) : null,
                'end_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->end_time)) : null,
                'teacher'  => $this->videoSession ? $this->videoSession->teacher : null,
                'name' => $this->name == null ? $num : $this->name,
                'price' => $this->price == null ? ($this->videoSession ? $this->videoSession->price : null) : $this->price,
                'product' => $this->product,
                'video_session_type' => $this->videoSession ? $this->videoSession->video_session_type : null,
                'video_link' => $this->videoSession ? $this->videoSession->video_link : null,
                'extraordinary' => $this->extraordinary,
                'is_hidden' => $this->is_hidden,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}

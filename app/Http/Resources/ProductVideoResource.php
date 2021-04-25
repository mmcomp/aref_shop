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
            $numToWord = $this->foo[$this->id] ? $number->numberToWords($this->foo[$this->id]) : $this->product_detail_video_name;
            if (!$this->foo[$this->id]) {
                $num = $numToWord;
            } else {
                $num = strpos($numToWord, "سه") !== false ? str_replace("سه", "سو", $numToWord) . 'م' : $numToWord . 'م';
            }

            return [
                'id' => $this->product_detail_video ? $this->product_detail_video->id : 0,
                'start_date' => $this->start_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'teacher'  => $this->teacher,
                'name' => ($this->product_detail_video && $this->product_detail_video->name == null) ? $num : ($this->product_detail_video ? $this->product_detail_video->name : ''),
                'price' => ($this->product_detail_video && $this->product_detail_video->price == null) ? $this->price : ($this->product_detail_video ? $this->product_detail_video->price : 0),
                'product' => ($this->product_detail_video) ? $this->product_detail_video->product : null,
                'video_session_type' => $this->video_session_type,
                'video_link' => $this->video_link,
                'extraordinary' => ($this->product_detail_video) ? $this->product_detail_video->extraordinary : 0,
                'is_hidden' => ($this->product_detail_video) ? $this->product_detail_video->is_hidden : 0,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}

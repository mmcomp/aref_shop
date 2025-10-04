<?php

namespace App\Http\Resources;

use App\Utils\Number2Word;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductQuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'quiz'=> $this->quiz,
                'product'=> $this->product,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}

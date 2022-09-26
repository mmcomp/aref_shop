<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserForProductCommentResource;

class ProductCommentForUserShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
        if($this->resource != null) {
            return [
               "id" => $this->id,
               "user" => new UserForProductCommentResource($this->user),
               "comment" => $this->comment,
               "created_at" => $this->created_at
            ];
        }
    }
}

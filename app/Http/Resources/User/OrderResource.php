<?php

namespace App\Http\Resources\User;

use App\Http\Resources\UserResource;
use App\Http\Resources\User\OrderDetailCollection;
use App\Http\Resources\User\OrderVideoDetailCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            $items = [];
            foreach($this->orderDetail as $item) {
                if($item->orderVideoDetails) {
                  foreach($item->orderVideoDetails as $orderVideoDetail) {
                    $items[] = $orderVideoDetail;
                  }  
                } 
                
             }
            return [
                'id' => $this->id,
                'user' => new UserResource($this->user),
                'amount' => $this->amount,
                'comment' => $this->comment,
                'order_status' => $this->status,
                'orderDetail' => new OrderDetailCollection($this->orderDetail),
                'orderVideoDetail' => new OrderVideoDetailCollection($items)
            ];
        }
    }
}

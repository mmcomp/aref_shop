<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'desktop_image' => $this->desktop_image,
            'mobile_image' => $this->mobile_image,
            'link' => $this->link,
            'is_active' => $this->is_active == 1 ? true : false,
        ];
    }
}

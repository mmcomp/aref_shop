<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'desktop_image' => $this->desktop_image,
            'mobile_image' => $this->mobile_image,
            'link' => $this->link,
        ];
    }
}

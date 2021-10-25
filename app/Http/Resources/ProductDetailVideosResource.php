<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VideoSessionsResource;
use App\Http\Resources\FileResource;

class ProductDetailVideosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $files = [];
       
        if ($this->resource != null) {
            if($this->videoSession){
                foreach($this->videoSession->videoSessionFiles as $file){
                    if($file->file != null) {
                        $files[] = new FileResource($file->file);
                    }
                }
            }
            return [
                'id' => $this->id,
                'name' => $this->name,
                'product' => $this->product,
                'price' => $this->price,
                'files' => $files,
                'video_session' => new VideoSessionsResource($this->videoSession),
                'extraordinary' => $this->extraordinary,
                'is_hidden' => $this->is_hidden,
                'single_purchase' => $this->single_purchase,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'free_conference_start_mode' => $this->free_conference_start_mode,
                'free_conference_description' => $this->free_conference_description,
            ];
        }
    }
}

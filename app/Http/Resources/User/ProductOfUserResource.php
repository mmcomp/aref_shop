<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\CategoryOnesResource;
use App\Http\Resources\CategoryTwosResource;
use App\Http\Resources\CategoryThreesResource;
use Illuminate\Support\Facades\Auth;

class ProductOfUserResource extends JsonResource
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
        $arrOfBoughtProducts = [];
        if ($this->resource != null) {
            if ($this->productFiles) {
                foreach ($this->productFiles as $file) {
                    if ($file->file != null) {
                        $files[] = new FileResource($file->file);
                    }
                }
            }
            if ($this->userProducts != null) {
                foreach ($this->userProducts as $product) {

                    if ($product->user) {
                        $user_phone = $product->user->email;
                        $whiteListed = in_array($user_phone, explode(",", env('EXECPTIONAL_USER')));
                        if (($whiteListed || ($product->created_at >= env('USER_PRODUCT_DATE'))) && ($product->user->id == Auth::user()->id)) {
                            $arrOfBoughtProducts[] = new ProductOfUserResource($product);
                        }
                    }
                }
            }

            return [
                'id' => $this->id,
                'buyed_before' =>  count($arrOfBoughtProducts) ? true : false,
                'name' => $this->name,
                'short_description' => $this->short_description,
                'long_description' => $this->long_description,
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'sale_expire' => $this->sale_expire,
                'video_props' => $this->video_props,
                'category_one' => new CategoryOnesResource($this->category_ones),
                'category_two' => new CategoryTwosResource($this->category_twos),
                'category_three' => new CategoryThreesResource($this->category_threes),
                'main_image_path' => $this->main_image_path,
                'main_image_thumb_path' => $this->main_image_thumb_path,
                'second_image_path' => $this->second_image_path,
                'files' => $files,
                'published' => $this->published,
                'type' => $this->type,
                'quiz24_data' => json_decode($this->quiz24_data),
                'special' => $this->special,
                "order_date" => $this->order_date,
                'education_system' => $this->education_system,
                'hour' => $this->hour,
                'days' => $this->days,
                'start_date' => $this->start_date,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}

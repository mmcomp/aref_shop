<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'short_description' => 'required|string|max:1500',
            'long_description' => 'required|string|max:2000',
            'price' => 'required|integer',
            'sale_price' => 'integer',
            'sale_expire' => 'date',
            'video_props' => 'string|max:1000',
            'category_ones_id' => 'integer',
            'category_twos_id' => 'integer',
            'category_threes_id' => 'integer',
            'category_fours_id' => 'integer',
            'main_image_path' => 'string|max:1000',
            'main_image_thumb_path' => 'string|max:1000',
            'second_image_path' => 'string|max:1000',
            'published' => 'required|integer',
            'type' => 'required|string|in:normal,download,chairs,video'
        ];
    }
     /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return \Illuminate\Http\JsonResponse
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) {
            $errors = (new ValidationException($validator))->errors();

            throw new HttpResponseException(
                response()->json(['errors' => $errors], 422)
            );
        }
    }
}

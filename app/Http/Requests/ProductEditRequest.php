<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


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
            'name' => 'string|min:3|max:255',
            'short_description' => 'string|max:1500',
            'long_description' => 'string|max:2000',
            'price' => 'integer',
            'sale_price' => 'nullable|integer|lte:price',
            'sale_expire' => 'date',
            'video_props' => 'nullable|string|max:1000',
            'category_ones_id' => [
                'integer',
                Rule::exists('category_ones', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'category_twos_id' => [
                'integer',
                Rule::exists('category_twos', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'category_threes_id' => [
                'integer',
                Rule::exists('category_threes', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'category_fours_id' => [
                'integer',
                Rule::exists('category_fours', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            // 'main_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'second_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'published' => 'integer',
            'type' => 'string|in:normal,download,chairs,video'
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
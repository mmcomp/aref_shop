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
            'short_description' => 'nullable|string|max:1500',
            'long_description' => 'nullable|string',
            'price' => 'integer',
            'sale_price' => 'nullable|integer|lte:price',
            'sale_expire' => 'nullable|date',
            'video_props' => 'nullable|string|max:1000',
            'category_ones_id' => [
                'integer',
                Rule::exists('category_ones', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'category_twos_id' => [
                'nullable',
                'integer',
                Rule::exists('category_twos', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'category_threes_id' => [
                'nullable',
                'integer',
                Rule::exists('category_threes', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'published' => 'integer',
            'type' => 'string|in:normal,download,chairs,video,package,quiz24',
            'special' => 'in:0,1',
            "order_date" =>
            [
                "required",
                "date"
            ],
            'education_system' => 'nullable|string|min:3|max:255',
            'hour' => 'nullable|string|min:3|max:255',
            'days' => 'nullable|string|min:3|max:255',
            'start_date' => 'nullable|string|min:3|max:255',
            'quiz24_data' => 'nullable|array',
            'quiz24_data.*' => 'integer|min:0|max:1000000000',
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

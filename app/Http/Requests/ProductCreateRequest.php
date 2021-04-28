<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;


class ProductCreateRequest extends FormRequest
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
            'short_description' => 'nullable|string|max:1500',
            'long_description' => 'nullable|string|max:2000',
            'price' => 'required|integer',
            'sale_price' => 'nullable|integer|lte:price',
            'sale_expire' => 'nullable|date',
            'video_props' => 'nullable|string|max:1000',
            'category_ones_id' => [
                'required',
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
            'published' => 'required|integer',
            'type' => 'required|string|in:normal,download,chairs,video',
            'special' => 'required|in:0,1'
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

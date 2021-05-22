<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssignVideoToProductRequest extends FormRequest
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
            'product_detail_videos_id' => [
                'required',
                'integer',
                Rule::exists('product_detail_videos', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'products_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'name' => 'nullable|required_if:extraordinary,1|string|min:3|max:255',
            'price' => 'nullable|integer',
            'extraordinary' => 'required|in:0,1',
            'single_purchase' => 'required|in:0,1',
            'is_hidden' => 'nullable|in:0,1'
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

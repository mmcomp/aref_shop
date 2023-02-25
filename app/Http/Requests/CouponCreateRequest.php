<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CouponCreateRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255|unique:coupons',
            'description' => 'nullable|string|min:3|max:1000',
            'amount' => 'required|integer',
            'type' => 'required|in:percent,amount',
            'expired_at' => 'nullable|date',
            'products_id' => [
                'required',
                'integer',
                Rule::exists('products','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                })
            ],
            'count' => 'required|integer'
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

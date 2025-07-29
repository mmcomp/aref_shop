<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportSaleRequest extends FormRequest
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
            'products_id' => [
                'required_if:mode,product',
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'product_detail_videos_id' => [
                'nullable',
                'integer',
                Rule::exists('product_detail_videos', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'users_id' => [
                'required_if:mode,order',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'mode'=> 'required|string|in:product,order,date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'school_id' => 'nullable|integer|exists:schools,id',
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AddMicroProductToCartRequest extends FormRequest
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
            'orders_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->where(function ($query) {
                    return $query->where('status', 'manual_waiting');
                }),
            ],
            'products_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                })
            ],
            'product_details_id' => 'nullable|integer',
            "type" => [
                "nullable",
                Rule::in(["chair","video"])
            ],
            "chairs" => [
                "nullable",
                "array",
                "required_if:type,chair",
                // Rule::exists('product_detail_chairs')->where(function($query)
                // {
                //    return $query->where('start','<=','chairs.*')->where('end','>=','chairs.*');
                // })
            ],
            "chairs.*" => [
                "nullable",
                "distinct",
                "integer",
                "min:1"
            ]
        ];
    }
    public function all($keys = null)
    {
        // Add route parameters to validation data
        $data = parent::all();
        $data['orders_id'] = $this->route('orders_id');
        return $data;
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
                response()->json(['errors' => $errors], 400)
            );
        }
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DeleteProductFromCartRequest extends FormRequest
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
            'order_details_id' => [
                'required',
                'integer',
                'exists:order_details,id'
            ], 
            "orders_id" => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->where(function ($query) {
                    return $query->where('status', 'manual_waiting');
                }),
            ]
        ];
    }
    public function all($keys = null)
    {
        // Add route parameters to validation data
        $data = parent::all();
        $data['order_details_id'] = $this->route('order_details_id');
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
                response()->json(['errors' => $errors], 422)
            );
        }
    }
}

<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class bpPayRequest extends FormRequest
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
            '‫‪terminalId‬‬' => 'required|integer',
            '‫‪userName‬‬' => 'required|string|min:3|max:255',
            '‫‪userPassword‬‬' => 'required|string|min:3|max:255',
            'orderId' => 'required|integer',
            'amount' => 'required|integer',
            'localDate' => '‫‪required|date',
            '‫‪localTime‬‬' => 'required|time',
            'additionalData' => 'required|string|min:3|max:1000',
            '‫‪callBackUrl‬‬' => 'required|url',
            '‫‪payerId‬‬' => 'required|integer'
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

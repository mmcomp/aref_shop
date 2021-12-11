<?php

namespace App\Http\Requests\User;

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
            'products_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                })
            ],
            'product_details_id' => 'nullable|integer',

            // 'type' => 'required_without:chairs',
            // 'chairs' => 'required_without:Email',

            'type' => [
                'nullable',
                Rule ::in(['chair','video'])
            ],            
            "chairs" => [
                'nullable',
                'array',
                "required_if:type,chair"
            ],
            "chairs.*" => ['nullable','distinct','integer','min:1']
           
            
            // 'type' => [
            //     'required_if:chairs.*,in:chair'
            //     // 'required',
            //     // Rule::exists(),
            //     // Rule ::in(['chair','video'])
            // ],
            // "chairs" => ['required_if:type','array'],
            // "chairs.*" => ['required','distinct','integer','min:1']
            // "names"    => "required|array|min:3",
            // "names.*"  => "required|string|distinct|min:3",
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
                response()->json(['errors' => $errors], 400)
            );
        }
    }
}

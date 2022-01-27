<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreProductPackageRequest extends FormRequest
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
            "product_id" =>[
                "required",
                "integer",
                 Rule::Exists("products","id")//->where(function($query){
                //     return true ; //$query->where("id",'>=',0);
                // })
            ],
            "child_product_ids" => [
                "required" ,
                "array"               
            ],
            "child_product_ids.*" =>['required','distinct','integer','min:1', Rule::Exists("products","id")]            
        ];
    }
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class storeProductByMobileListRequest extends FormRequest
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
           "products_id" => 
           [
                "required",
                "integer",
                Rule::exists("products","id")->where("id",$this->products_id)->where("is_deleted",false)
           ],
           "mobile_list" =>
           [
               "array",
               "required"
           ],
           "mobile_list.*" =>
           [
               "size:11",
               "required",
               "distinct"
           ]
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

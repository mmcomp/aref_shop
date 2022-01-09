<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class ShowFilteredTeamUserRequest extends FormRequest
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
           "mobile" => [
               "required",
               "size:11",
               "string",
           ]
        ];
    }    
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

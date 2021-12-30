<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;



class TeamUserCreateRequest extends FormRequest
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
            //"user_id_creator" => ["required", "int","unique:team_users,user_id_creator"],
            "name" => ["required", "string","min:3","unique:team_users,name"]
            //"is_full" => ["required", "bool"],
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

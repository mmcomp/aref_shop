<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ReplaceTeamMemberRequest extends FormRequest
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
            "team_user_id" => ["required" , "int"],
            "inactive_mobile" => [
                "required" ,
                 "string",
                 "size:11",
                 //Rule::Exists(-)
                ],
            "active_mobile" => ["required" , "string","size:11"]
        ];
    }
    public function handleError($validation)
    {
        if($validation->fails())
        {
           $errors= (new ValidationException($validation))->errors();
           throw new HttpResponseException(
             response()->json(["errors"=>$errors],422)
        );
        }
    }
}

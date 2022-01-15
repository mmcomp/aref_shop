<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TeamUserMemberEditRequest extends FormRequest
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
        //"team_user_id" => ["nullable", "int"/*,"unique:team_user_members,team_user_id"*/],
            "mobile" => ["nullable", "size:11"],
            //"is_verified" => ["required", "bool"],
        ];
    }
}

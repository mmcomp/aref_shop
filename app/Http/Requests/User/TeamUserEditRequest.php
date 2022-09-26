<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TeamUserEditRequest extends FormRequest
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
            "user_id_creator" => ["nullable", "int","uique:team_user,user_id_creator"],
            "name" => ["nullable", "string"],
            "is_full" => ["nullable", "bool"],
        ];
    }
}

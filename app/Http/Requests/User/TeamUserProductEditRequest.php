<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TeamUserProductEditRequest extends FormRequest
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
            "user_id_creator" => ["nullable", "int"],
            "team_user_id" => ["nullable", "int"],
            "product_id" => ["nullable", "int"],
        ];
    }
}

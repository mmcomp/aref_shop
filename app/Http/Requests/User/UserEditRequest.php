<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\theInt;

class UserEditRequest extends FormRequest
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
            'id' => ['required',new theInt],
            'first_name' => 'string|between:2,100',
            'last_name' => 'string|between:2,100',
            'email' => 'string|max:12',
            'password' => 'nullable|string|min:6|confirmed',
            'address' => 'nullable|min:10|max:1000',
            'postall' => 'nullable|digits:10',
            'cities_id' => [
                'nullable',
                'integer',
                Rule::exists('cities','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
        ];
    }
}

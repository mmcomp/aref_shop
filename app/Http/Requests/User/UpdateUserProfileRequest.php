<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|string|min:8|same:password',
            'avatar_path' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'postall' => 'nullable|string|max:255',
            'cities_id' => 'nullable|exists:cities,id',
        ];
    }
}

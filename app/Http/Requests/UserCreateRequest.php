<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|max:12|unique:users',
            'password' => 'required|string|min:6',
            'referrer_users_id' => [
                'required',
                'integer',
                Rule::exists('users','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'address' => 'required|min:10|max:1000',
            'postall' => 'required|digits:10',
            'cities_id' => [
                'required',
                'integer',
                Rule::exists('cities','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
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

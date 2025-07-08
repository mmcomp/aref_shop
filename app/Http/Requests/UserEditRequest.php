<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
            'first_name' => 'nullable|string|between:2,100',
            'last_name' => 'string|between:2,100',
            'email' => 'string|max:12',
            'password' => 'nullable|string|min:6|confirmed',
            'gender' => 'nullable|in:male,female',
            'referrer_users_id' => [
                'nullable',
                'different:id',
                new theInt,
                Rule::exists('users','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'address' => 'nullable|min:10|max:1000',
            'postall' => 'nullable|digits:10',
            'cities_id' => [
                'nullable',
                'integer',
                Rule::exists('cities','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'groups_id' => [
                'integer',
                Rule::exists('groups','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'reading_station_id' => 'nullable|exists:reading_stations,id',
            'disabled' => 'nullable|boolean',
            'school_id' => [
                'nullable',
                'integer',
                Rule::exists('schools','id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'major' => 'nullable|string|between:3,100',
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

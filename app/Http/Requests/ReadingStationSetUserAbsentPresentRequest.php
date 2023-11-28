<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReadingStationSetUserAbsentPresentRequest extends FormRequest
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
            'reading_station_slut_user_exit_id' => 'nullable|exists:reading_station_sluts,id',
            'possible_end' => 'nullable|date_format:H:i',
            'end' => 'nullable|date_format:H:i',
            'posssible_exit_way' => 'nullable|in:taxi,mother,father,relatives,parents_notified,tillnight,self',
            'exit_way' => 'nullable|in:taxi,mother,father,relatives,parents_notified,tillnight,self',
            'enter_way' => 'nullable|in:mother,father,relatives',
            'is_optional_visit' => 'nullable|bool',
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

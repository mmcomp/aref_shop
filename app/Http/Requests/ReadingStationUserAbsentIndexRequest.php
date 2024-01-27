<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReadingStationUserAbsentIndexRequest extends FormRequest
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
            'sort' => 'required_with:type|in:id,day,reading_station_absent_reason_id,reading_station_slut_id,absense_approved_status',
            'sort_dir' => 'required_with:sort|in:asc,desc',
            'per_page' => 'string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'from_day' => 'required|date_format:Y-m-d',
            'to_day' => 'required|date_format:Y-m-d',
            'reading_station_slut_id' => 'nullable|exists:reading_station_sluts,id',
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

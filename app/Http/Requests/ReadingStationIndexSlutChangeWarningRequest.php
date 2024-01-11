<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReadingStationIndexSlutChangeWarningRequest extends FormRequest
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
            'per_page' => 'string|max:255',
            'sort' => 'required_with:type|in:id,reading_station_slut_user_id,description,operator_id,is_read,reader_id',
            'sort_dir' => 'required_with:sort|in:asc,desc',
            'is_read' => 'nullable|in:true,false',
            'reading_station_slut_user_id' => 'nullable|exists:reading_station_slut_users,id',
            'mark_as_read' => 'nullable|in:true,false',
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

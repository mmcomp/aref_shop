<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditSingleSessionRequest extends FormRequest
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
            'id' => [
                'required',
                'integer',
                'exists:product_detail_videos,id'
            ],
            'date' => 'date',
            'from_time' => 'date_format:H:i',
            'to_time' => 'date_format:H:i|after:from_time',
            'price' => 'integer',
            'name' => 'required_if:extraordinary,1|nullable|string|min:3|max:255',
            'products_id' => [
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'extraordinary' => 'in:0,1',
            'single_purchase' => 'in:0,1',
            'is_hidden' => 'in:0,1',
            'video_link' => "nullable",//'nullable|url',
            'is_aparat' =>  "nullable | boolean",
            'is_sky_room' =>  "nullable | boolean",
            'video_session_type' => 'nullable|in:online,offline',
            'free_conference_start_mode' => 'in:playPage,productPage',
            'free_conference_description' => 'nullable|string',
            'free_conference_before_start_text' => 'nullable|string',
        ];
    }
    public function all($keys = null)
    {
        // Add route parameters to validation data
        $data = parent::all();
        $data['id'] = $this->route('id');
        return $data;
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FileCreateRequest extends FormRequest
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
            'file' => 'file|mimes:xls,xlsx,docx,doc,zip,pdf,jpg,png|max:'.env('FILE_SIZE') * 1024,
            'name' => 'string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'products_id' => [
                'required_if:product_detail_videos_id,null',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'product_detail_videos_id' => [
                'required_if:products_id,null',
                'integer',
                Rule::exists('product_detail_videos', 'id')->where(function ($query) {
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

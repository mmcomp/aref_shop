<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ProductDetailVideosEditRequest extends FormRequest
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
            'name' => 'string|min:3|max:255',
            'products_id' => [
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'price' => 'integer',
            'video_sessions_id' => [
                'integer',
                Rule::exists('video_sessions', 'id')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'extraordinary' => 'in:0,1',
            'single_purchase' => 'in:0,1' ,
            'is_hidden' => 'in:0,1',
            'free_conference_description'=> 'nullable|string',
            'free_conference_start_mode'=>'in:playPage,productPage'
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

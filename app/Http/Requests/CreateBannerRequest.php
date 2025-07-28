<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBannerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'desktop_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'required|url',
        ];
    }
}

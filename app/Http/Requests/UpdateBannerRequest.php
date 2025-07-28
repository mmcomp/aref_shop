<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'nullable|url',
            'is_active' => 'nullable|in:0,1',
        ];
    }
}

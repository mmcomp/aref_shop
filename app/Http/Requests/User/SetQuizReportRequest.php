<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SetQuizReportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'examCode' => 'required|string',
            'report' => 'required|string',
            'user_mobile' => 'required|exists:users,email',
        ];
    }
}

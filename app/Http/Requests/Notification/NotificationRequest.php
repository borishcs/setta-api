<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Title é obrigatório',
            'title.min' => 'Title deve ter ao menos 3 caracteres',
            'title.max' => 'Title deve ter no máximo 255 caracteres',
        ];
    }
}

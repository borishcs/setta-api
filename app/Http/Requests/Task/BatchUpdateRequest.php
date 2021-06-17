<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $fieldRules = [
            'id' => 'required|string',
            'title' => 'nullable|string|min:3|max:255',
            'period' => 'nullable|string|max:30',
            'when' => 'nullable|string|max:30',
        ];

        $rules = [];

        foreach ($this->request->all() as $key => $item) {
            foreach ($item as $field => $value) {
                $rules[$key . '.' . $field] = $fieldRules[$field];
            }
        }

        return $rules;
    }
}

<?php

namespace App\Http\Requests\Planner;

use Illuminate\Foundation\Http\FormRequest;

class PlannerOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $fieldRules = [
            'id' => 'required|integer',
            'order' => 'required|integer',
            'period' => 'required|integer',
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

<?php

namespace App\Http\Requests\Timer;

use Illuminate\Foundation\Http\FormRequest;

class TimerRequest extends FormRequest
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
            'task_id' => 'nullable|integer',
            'habit_id' => 'nullable|integer',
            'tag_id' => 'nullable|integer',
            'estimated_time' => 'required|date_format:H:i:s',
            'estimated_used_time' => 'required|date_format:H:i:s',
            'rest_time' => 'required|date_format:H:i:s',
            'rest_used_time' => 'required|date_format:H:i:s',
            'started_at' => 'required|date_format:Y-m-d H:i:s',
            'finished_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}

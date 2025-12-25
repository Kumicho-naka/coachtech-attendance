<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'breaks.*.start_time' => 'nullable|date_format:H:i',
            'breaks.*.end_time' => 'nullable|date_format:H:i|after:breaks.*.start_time',
            'remarks' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start_time.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
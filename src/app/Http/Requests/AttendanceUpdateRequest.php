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
            'remarks' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $breaks = $this->input('breaks', []);

            // 出勤時間のフォーマットチェック（秒付きも許可）
            if ($startTime && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $startTime)) {
                $validator->errors()->add('start_time', '出勤時間が不適切な値です');
                return;
            }

            // 退勤時間のフォーマットチェック（秒付きも許可）
            if ($endTime && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $endTime)) {
                $validator->errors()->add('end_time', '退勤時間が不適切な値です');
                return;
            }

            // 出勤時間が退勤時間より後の場合
            if ($startTime && $endTime && $startTime > $endTime) {
                $validator->errors()->add('start_time', '出勤時間が不適切な値です');
            }

            if (!$endTime) {
                return;
            }

            foreach ($breaks as $index => $break) {
                $breakStart = $break['start_time'] ?? null;
                $breakEnd = $break['end_time'] ?? null;

                // 空の休憩はスキップ
                if (($breakStart === null || $breakStart === '') && ($breakEnd === null || $breakEnd === '')) {
                    continue;
                }

                // フォーマットチェック（秒付きも許可）
                if ($breakStart && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $breakStart)) {
                    $validator->errors()->add("breaks.{$index}.start_time", '休憩時間が不適切な値です');
                    continue;
                }
                if ($breakEnd && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $breakEnd)) {
                    $validator->errors()->add("breaks.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                    continue;
                }

                // 片方だけ入力された場合はエラー
                if (!$breakStart && $breakEnd) {
                    $validator->errors()->add("breaks.{$index}.start_time", '休憩時間が不適切な値です');
                    continue;
                }
                if ($breakStart && !$breakEnd) {
                    $validator->errors()->add("breaks.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                    continue;
                }

                if ($startTime && $breakStart && $breakStart < $startTime) {
                    $validator->errors()->add("breaks.{$index}.start_time", '休憩時間が不適切な値です');
                }

                if ($breakStart && $breakStart > $endTime) {
                    $validator->errors()->add("breaks.{$index}.start_time", '休憩時間が不適切な値です');
                }

                if ($breakStart && $breakEnd && $breakEnd <= $breakStart) {
                    $validator->errors()->add("breaks.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($breakEnd && $breakEnd > $endTime) {
                    $validator->errors()->add("breaks.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }
}

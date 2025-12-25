<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakCorrectionRequest;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 自分の勤怠かチェック
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 承認待ちの申請があるかチェック
        $hasPendingRequest = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('attendance.detail', compact('attendance', 'hasPendingRequest'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 自分の勤怠かチェック
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 修正申請を作成
        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        // 休憩の修正申請を作成
        if ($request->has('breaks')) {
            foreach ($request->breaks as $index => $breakData) {
                BreakCorrectionRequest::create([
                    'attendance_correction_request_id' => $correctionRequest->id,
                    'break_id' => $breakData['id'] ?? null,
                    'start_time' => $breakData['start_time'] ?? null,
                    'end_time' => $breakData['end_time'] ?? null,
                ]);
            }
        }

        return redirect()->route('stamp-correction-request.list')->with('message', '修正申請を送信しました。');
    }
}
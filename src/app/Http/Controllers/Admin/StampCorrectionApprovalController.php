<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;
use App\Models\RestBreak;
use Carbon\Carbon;

class StampCorrectionApprovalController extends Controller
{
    public function show($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with([
            'user',
            'attendance',
            'breakCorrectionRequests'
        ])->findOrFail($id);

        return view('admin.requests.detail', ['request' => $correctionRequest]);
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = AttendanceCorrectionRequest::findOrFail($id);

        // 勤怠情報を更新
        $attendance = $correctionRequest->attendance;
        $attendance->update([
            'start_time' => $correctionRequest->start_time,
            'end_time' => $correctionRequest->end_time,
            'remarks' => $correctionRequest->remarks,
        ]);

        // 既存の休憩を削除
        RestBreak::where('attendance_id', $attendance->id)->delete();

        // 修正申請の休憩データを反映
        foreach ($correctionRequest->breakCorrectionRequests as $breakCorrection) {
            if ($breakCorrection->start_time && $breakCorrection->end_time) {
                RestBreak::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $breakCorrection->start_time,
                    'end_time' => $breakCorrection->end_time,
                ]);
            }
        }

        // 修正申請を承認済みに更新
        $correctionRequest->update([
            'status' => 'approved',
            'approved_at' => Carbon::now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('admin.stamp-correction-request.list')->with('message', '修正申請を承認しました。');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RestBreak;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 勤怠情報を更新
        $attendance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'remarks' => $request->remarks,
        ]);

        // 既存の休憩を削除
        RestBreak::where('attendance_id', $attendance->id)->delete();

        // 新しい休憩データを作成
        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                if (!empty($breakData['start_time']) && !empty($breakData['end_time'])) {
                    RestBreak::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $breakData['start_time'],
                        'end_time' => $breakData['end_time'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.list')->with('message', '勤怠情報を修正しました。');
    }
}
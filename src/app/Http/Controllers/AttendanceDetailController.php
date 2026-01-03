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

        // 承認待ちの申請を取得
        $pendingRequest = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->with('breakCorrectionRequests')
            ->first();

        $hasPendingRequest = !is_null($pendingRequest);

        // 表示用データを準備（申請内容 or 現在の内容）
        $displayData = [
            'start_time' => $hasPendingRequest
                ? $pendingRequest->start_time
                : $attendance->start_time,
            'end_time' => $hasPendingRequest
                ? $pendingRequest->end_time
                : $attendance->end_time,
            'remarks' => $hasPendingRequest
                ? $pendingRequest->remarks
                : $attendance->remarks,
            'breaks' => $hasPendingRequest
                ? $pendingRequest->breakCorrectionRequests
                : $attendance->breaks,
        ];

        // 承認待ちでない場合、空の休憩入力欄を1つ追加
        $breaksWithBlank = $displayData['breaks'];
        if (!$hasPendingRequest) {
            $breaksWithBlank = $displayData['breaks']->toArray();
            $breaksWithBlank[] = null; // 空の休憩入力欄
        }

        return view('attendance.detail', compact('attendance', 'hasPendingRequest', 'pendingRequest', 'displayData', 'breaksWithBlank'));
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
                // 両方入力されている場合のみ登録
                if (!empty($breakData['start_time']) && !empty($breakData['end_time'])) {
                    BreakCorrectionRequest::create([
                        'attendance_correction_request_id' => $correctionRequest->id,
                        'break_id' => $breakData['id'] ?? null,
                        'start_time' => $breakData['start_time'],
                        'end_time' => $breakData['end_time'],
                    ]);
                }
            }
        }

        return redirect()->route('stamp-correction-request.list')->with('message', '修正申請を送信しました。');
    }
}

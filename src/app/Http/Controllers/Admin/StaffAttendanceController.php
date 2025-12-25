<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('breaks')
            ->orderBy('date', 'asc')
            ->get();

        $currentDate = Carbon::create($year, $month, 1);

        return view('admin.staff.attendance', compact('user', 'attendances', 'currentDate'));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('breaks')
            ->orderBy('date', 'asc')
            ->get();

        $filename = sprintf('%s_%d年%d月_勤怠データ.csv', $user->name, $year, $month);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            // BOM追加（Excel対応）
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー
            fputcsv($stream, ['日付', '出勤時刻', '退勤時刻', '休憩時間', 'ステータス', '備考']);

            foreach ($attendances as $attendance) {
                $breakTime = $this->calculateBreakTime($attendance->breaks);

                fputcsv($stream, [
                    $attendance->date,
                    $attendance->start_time ?? '',
                    $attendance->end_time ?? '',
                    $breakTime,
                    $this->getStatusLabel($attendance->status),
                    $attendance->remarks ?? '',
                ]);
            }

            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateBreakTime($breaks)
    {
        $totalMinutes = 0;

        foreach ($breaks as $break) {
            if ($break->start_time && $break->end_time) {
                $start = Carbon::parse($break->start_time);
                $end = Carbon::parse($break->end_time);
                $totalMinutes += $end->diffInMinutes($start);
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d時間%d分', $hours, $minutes);
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'outside' => '勤務外',
            'working' => '出勤中',
            'resting' => '休憩中',
            'finished' => '退勤済',
        ];

        return $labels[$status] ?? '';
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // その月の勤怠データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('breaks')
            ->orderBy('date', 'asc')
            ->get()
            ->mapWithKeys(function ($attendance) {
                // 明示的にY-m-d形式のキーを作成
                return [Carbon::parse($attendance->date)->format('Y-m-d') => $attendance];
            });

        $currentDate = Carbon::create($year, $month, 1);

        // その月の全日付を生成
        $daysInMonth = $currentDate->daysInMonth;
        $allDates = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $allDates[] = [
                'date' => $date,
                'attendance' => $attendances->get($date), // 勤怠データがあればそれを使用、なければnull
            ];
        }

        return view('attendance.list', compact('allDates', 'currentDate'));
    }
}

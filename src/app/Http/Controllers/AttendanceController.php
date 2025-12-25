<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RestBreak;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $onBreak = false;
        if ($todayAttendance) {
            $onBreak = $todayAttendance->status === 'resting';
        }

        $currentDateTime = Carbon::now();

        return view('attendance.index', compact('todayAttendance', 'onBreak', 'currentDateTime'));
    }

    public function start(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now()->format('H:i:s'),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakStart(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            RestBreak::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now()->format('H:i:s'),
            ]);

            $attendance->update(['status' => 'resting']);
        }

        return redirect()->route('attendance.index');
    }

    public function breakEnd(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $latestBreak = RestBreak::where('attendance_id', $attendance->id)
                ->whereNull('end_time')
                ->latest()
                ->first();

            if ($latestBreak) {
                $latestBreak->update([
                    'end_time' => Carbon::now()->format('H:i:s'),
                ]);
            }

            $attendance->update(['status' => 'working']);
        }

        return redirect()->route('attendance.index');
    }

    public function end(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $attendance->update([
                'end_time' => Carbon::now()->format('H:i:s'),
                'status' => 'finished',
            ]);
        }

        return redirect()->route('attendance.index')->with('message', 'お疲れ様でした。');
    }
}

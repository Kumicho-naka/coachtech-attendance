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

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('breaks')
            ->orderBy('date', 'asc')
            ->get();

        $currentDate = Carbon::create($year, $month, 1);

        return view('attendance.list', compact('attendances', 'currentDate'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $attendances = Attendance::where('date', $date)
            ->with(['user', 'breaks'])
            ->orderBy('user_id', 'asc')
            ->get();

        $currentDate = Carbon::parse($date);

        return view('admin.attendance.index', compact('attendances', 'currentDate'));
    }
}
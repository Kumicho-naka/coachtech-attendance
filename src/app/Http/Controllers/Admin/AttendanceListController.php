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

        // その日の勤怠データを取得
        $attendances = Attendance::where('date', $date)
            ->with(['user', 'breaks'])
            ->orderBy('user_id', 'asc')
            ->get()
            ->keyBy('user_id'); // user_idをキーにする

        // 全ての一般ユーザーを取得
        $users = \App\Models\User::where('role', 'general')
            ->orderBy('id', 'asc')
            ->get();

        // 全ユーザーのデータを生成
        $allUserAttendances = [];
        foreach ($users as $user) {
            $allUserAttendances[] = [
                'user' => $user,
                'attendance' => $attendances->get($user->id), // 勤怠データがあればそれを使用、なければnull
            ];
        }

        $currentDate = Carbon::parse($date);

        return view('admin.attendance.index', compact('allUserAttendances', 'currentDate'));
    }
}

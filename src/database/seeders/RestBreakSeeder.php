<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\RestBreak;

class RestBreakSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // 休憩1回目（昼休憩）
            RestBreak::create([
                'attendance_id' => $attendance->id,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ]);

            // 休憩2回目（午後の休憩）
            RestBreak::create([
                'attendance_id' => $attendance->id,
                'start_time' => '15:00:00',
                'end_time' => '15:15:00',
            ]);
        }
    }
}
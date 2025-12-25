<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $generalUsers = [2, 3, 4]; // 一般ユーザーのID

        foreach ($generalUsers as $userId) {
            // 過去30日分の勤怠データを作成
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::today()->subDays($i);

                // 土日はスキップ
                if ($date->isWeekend()) {
                    continue;
                }

                Attendance::create([
                    'user_id' => $userId,
                    'date' => $date,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'status' => 'finished',
                ]);
            }
        }
    }
}
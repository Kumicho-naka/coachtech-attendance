<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_view_all_staff_list(): void
    {
        $admin = $this->createAdmin();
        $user1 = $this->createUser(['name' => 'テスト太郎', 'email' => 'test1@example.com']);
        $user2 = $this->createUser(['name' => 'テスト花子', 'email' => 'test2@example.com']);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('test1@example.com');
        $response->assertSee('テスト花子');
        $response->assertSee('test2@example.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_view_staff_attendance_list(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['name' => 'テスト太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => Carbon::now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee($attendance->start_time->format('H:i'));
        $response->assertSee($attendance->end_time->format('H:i'));
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_navigate_staff_attendance_to_previous_month(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $lastMonth = Carbon::now()->subMonth();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $lastMonth,
            'start_time' => $lastMonth->copy()->setTime(9, 0),
            'end_time' => $lastMonth->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?year={$lastMonth->year}&month={$lastMonth->month}");

        $response->assertStatus(200);
        $response->assertSee('前月');
        $response->assertSee('翌月');
    }

    /**
     * 「翌月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_navigate_staff_attendance_to_next_month(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $nextMonth = Carbon::now()->addMonth();

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?year={$nextMonth->year}&month={$nextMonth->month}");

        $response->assertStatus(200);
        $response->assertSee('前月');
        $response->assertSee('翌月');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_can_navigate_to_attendance_detail_from_staff_page(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => Carbon::now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
    }

    /**
     * 「CSV出力」ボタンで勤怠一覧情報がCSVでダウンロードできる
     */
    public function test_admin_can_export_staff_attendance_csv(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/staff/{$user->id}/csv", [
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}

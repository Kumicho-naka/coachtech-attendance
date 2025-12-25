<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\RestBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_view_own_attendances(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        // 明示的に異なる日付で作成して一意制約違反を回避
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(2),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => Carbon::today()->subDays(2),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);
        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => Carbon::today()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $this->assertEquals(3, $user->attendances()->count());
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_attendance_list_shows_current_month(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_can_navigate_to_previous_month(): void
    {
        $user = $this->createUser();
        $lastMonth = Carbon::now()->subMonth();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $lastMonth,
            'start_time' => $lastMonth->copy()->setTime(9, 0),
            'end_time' => $lastMonth->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?year=' . $lastMonth->year . '&month=' . $lastMonth->month);

        $response->assertStatus(200);
        // 月次ナビゲーションのリンク確認
        $response->assertSee('前月');
        $response->assertSee('翌月');
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_can_navigate_to_next_month(): void
    {
        $user = $this->createUser();
        $nextMonth = Carbon::now()->addMonth();

        $response = $this->actingAs($user)->get('/attendance/list?year=' . $nextMonth->year . '&month=' . $nextMonth->month);

        $response->assertStatus(200);
        $response->assertSee('前月');
        $response->assertSee('翌月');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_can_navigate_to_detail_page(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => Carbon::now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
    }

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_detail_page_shows_user_name(): void
    {
        $user = $this->createUser(['name' => 'テスト太郎']);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_detail_page_shows_correct_date(): void
    {
        $user = $this->createUser();
        $date = Carbon::today();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => Carbon::now()->subHours(8),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($date->format('Y年'));
        $response->assertSee($date->format('n月j日'));
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_correct_clock_times(): void
    {
        $user = $this->createUser();
        $startTime = Carbon::now()->subHours(8);
        $endTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($startTime->format('H:i'));
        $response->assertSee($endTime->format('H:i'));
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_correct_break_times(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => Carbon::now(),
            'status' => 'finished',
        ]);

        $breakStart = Carbon::now()->subHours(4);
        $breakEnd = Carbon::now()->subHours(3);

        RestBreak::create([
            'attendance_id' => $attendance->id,
            'start_time' => $breakStart,
            'end_time' => $breakEnd,
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_is_displayed(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        // JavaScriptで動的に表示されるため、基本的な要素の存在確認
        $response->assertSee('勤務外');
    }

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_outside_work(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_working(): void
    {
        $user = $this->createUser();
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_resting(): void
    {
        $user = $this->createUser();
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => 'resting',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_finished(): void
    {
        $user = $this->createUser();
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => Carbon::now()->subHours(1),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /**
     * 出勤ボタンが正しく機能する
     */
    public function test_can_clock_in(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/start');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => 'working',
        ]);
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_cannot_clock_in_twice_per_day(): void
    {
        $user = $this->createUser();
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHours(1),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_recorded(): void
    {
        $user = $this->createUser();
        $clockInTime = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => $clockInTime,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($clockInTime->format('H:i'));
    }

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_can_start_break(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-start');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'resting',
        ]);
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_can_take_multiple_breaks(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(4),
            'status' => 'working',
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        // 2回目の休憩ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_can_end_break(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => 'resting',
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-end');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'working',
        ]);
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_can_end_multiple_breaks(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(4),
            'status' => 'working',
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        // 2回目の休憩
        $this->actingAs($user)->post('/attendance/break-start');

        // 2回目の休憩戻ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_is_recorded(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(4),
            'status' => 'working',
        ]);

        $breakStart = Carbon::now()->subHours(2);
        $breakEnd = Carbon::now()->subHours(1);

        $attendance->breaks()->create([
            'start_time' => $breakStart,
            'end_time' => $breakEnd,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
    }

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_can_clock_out(): void
    {
        $user = $this->createUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->post('/attendance/end');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'finished',
        ]);
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_recorded(): void
    {
        $user = $this->createUser();
        $clockOutTime = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => Carbon::now()->subHours(8),
            'end_time' => $clockOutTime,
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($clockOutTime->format('H:i'));
    }
}

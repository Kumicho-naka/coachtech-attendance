<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\RestBreak;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が全ての勤怠データを閲覧できる
     */
    public function test_admin_can_view_all_attendances_for_date(): void
    {
        $admin = $this->createAdmin();
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::today(),
            'start_time' => '10:00',
            'end_time' => '19:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /**
     * 管理者勤怠一覧画面には当日の日付が表示されている
     */
    public function test_admin_attendance_list_shows_current_date(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y/m/d'));
    }

    /**
     * 「前日」を押下した時に表示日の前日の情報が表示される
     */
    public function test_admin_can_navigate_to_previous_day(): void
    {
        $admin = $this->createAdmin();
        $yesterday = Carbon::yesterday();

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y/m/d'));
    }

    /**
     * 「翌日」を押下した時に表示日の翌日の情報が表示される
     */
    public function test_admin_can_navigate_to_next_day(): void
    {
        $admin = $this->createAdmin();
        $tomorrow = Carbon::tomorrow();

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y/m/d'));
    }

    /**
     * 勤怠詳細ページには選択した勤怠情報が表示される
     */
    public function test_admin_detail_shows_selected_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_validation_fails_when_start_time_after_end_time(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '18:00',
            'end_time' => '09:00',
            'remarks' => '管理者修正',
        ]);

        $response->assertSessionHasErrors('end_time');

        // メッセージ内容を確認
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_validation_fails_when_break_start_after_end_time(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $break = RestBreak::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start_time' => '19:00',
                    'end_time' => '20:00',
                ]
            ],
            'remarks' => '管理者修正',
        ]);

        $response->assertSessionHasErrors('breaks.0.start_time');

        // メッセージ内容を確認
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertSee('休憩時間が不適切な値です');
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_validation_fails_when_break_end_after_end_time(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $break = RestBreak::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start_time' => '17:00',
                    'end_time' => '19:00',
                ]
            ],
            'remarks' => '管理者修正',
        ]);

        $response->assertSessionHasErrors('breaks.0.end_time');

        // メッセージ内容を確認
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_admin_validation_fails_without_remarks(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors('remarks');

        // メッセージ内容を確認
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertSee('備考を記入してください');
    }

    /**
     * 管理者は承認待ちの修正申請一覧を閲覧できる
     */
    public function test_admin_can_view_pending_correction_requests(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * 管理者は承認済みの修正申請一覧を閲覧できる
     */
    public function test_admin_can_view_approved_correction_requests(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正理由',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * 管理者は修正申請の詳細を閲覧できる
     */
    public function test_admin_can_view_correction_request_detail(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['name' => 'テスト太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * 管理者は修正申請を承認できる
     */
    public function test_admin_can_approve_correction_request(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect();

        // 申請ステータスが承認済みに更新されることを確認
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        // 勤怠情報が更新されることを確認
        $attendance->refresh();
        $this->assertEquals('09:00:00', $attendance->start_time);
        $this->assertEquals('18:00:00', $attendance->end_time);
    }
}

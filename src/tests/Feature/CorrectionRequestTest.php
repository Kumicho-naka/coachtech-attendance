<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\RestBreak;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_fails_when_start_time_after_end_time(): void
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'start_time' => '18:00',
            'end_time' => '09:00',
            'remarks' => '修正理由',
        ]);

        $response->assertSessionHasErrors('start_time');

        // メッセージ内容を確認
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('出勤時間が不適切な値です');
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_fails_when_break_start_after_end_time(): void
    {
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

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start_time' => '19:00',
                    'end_time' => '20:00',
                ]
            ],
            'remarks' => '修正理由',
        ]);

        $response->assertSessionHasErrors('breaks.0.start_time');

        // メッセージ内容を確認
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('休憩時間が不適切な値です');
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_fails_when_break_end_after_end_time(): void
    {
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

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start_time' => '17:00',
                    'end_time' => '19:00',
                ]
            ],
            'remarks' => '修正理由',
        ]);

        $response->assertSessionHasErrors('breaks.0.end_time');

        // メッセージ内容を確認
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_validation_fails_without_remarks(): void
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors('remarks');

        // メッセージ内容を確認
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('備考を記入してください');
    }

    /**
     * 修正申請を送信できる
     */
    public function test_can_submit_correction_request(): void
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '修正理由',
        ]);

        $response->assertRedirect(route('stamp-correction-request.list'));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '09:30:00',
            'end_time' => '18:30:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);
    }

    /**
     * ユーザーは自分の承認待ち申請一覧を閲覧できる
     */
    public function test_user_can_view_pending_requests(): void
    {
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

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    /**
     * ユーザーは自分の承認済み申請一覧を閲覧できる
     */
    public function test_user_can_view_approved_requests(): void
    {
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

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    /**
     * 申請詳細ページに遷移できる
     */
    public function test_can_navigate_to_request_detail(): void
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => 'finished',
        ]);

        // 承認待ちの申請を作成
        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('承認待ちのため修正はできません');
    }
}

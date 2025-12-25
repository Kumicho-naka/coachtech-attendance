<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     */
    public function test_verification_email_sent_after_registration(): void
    {
        Notification::fake();

        // 1. 会員登録をする
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 期待挙動: 登録したメールアドレス宛に認証メールが送信されている
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_verification_notice_screen_has_link_to_mail_service(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 1. メール認証導線画面を表示する
        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();

        // 2. 「認証はこちらから」ボタンが表示されていることを確認
        $response->assertSee('認証はこちらから');

        // mailtrapの場合: メール内のリンクをクリックする案内が表示される
        $response->assertSee('送信されたメールに記載されているリンクをクリックして認証を完了してください');
    }

    /**
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_user_redirected_to_attendance_after_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 1. メール認証を完了する（メール認証URLをシミュレート）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // 期待挙動: 勤怠登録画面に遷移する
        $response->assertRedirect('/attendance?verified=1');

        // メール認証が完了していることを確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

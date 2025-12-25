<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションエラーが表示される
     */
    public function test_registration_fails_without_name(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * メールアドレスが未入力の場合、バリデーションエラーが表示される
     */
    public function test_registration_fails_without_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * パスワードが8文字未満の場合、バリデーションエラーが表示される
     */
    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * パスワードと確認用パスワードが一致しない場合、バリデーションエラーが表示される
     */
    public function test_registration_fails_with_password_mismatch(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーが表示される
     */
    public function test_registration_fails_without_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 正しい情報で会員登録ができる
     */
    public function test_user_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'テスト太郎',
        ]);
    }

    /**
     * メールアドレスが未入力の場合、ログインできない
     */
    public function test_login_fails_without_email(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * パスワードが未入力の場合、ログインできない
     */
    public function test_login_fails_without_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 誤った認証情報でログインできない
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 一般ユーザーはログアウトできる
     */
    public function test_user_can_logout(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

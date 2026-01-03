<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、管理者ログインできない
     */
    public function test_admin_login_fails_without_email(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        // メッセージ内容を確認
        $response = $this->get('/admin/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    /**
     * パスワードが未入力の場合、管理者ログインできない
     */
    public function test_admin_login_fails_without_password(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        // メッセージ内容を確認
        $response = $this->get('/admin/login');
        $response->assertSee('パスワードを入力してください');
    }

    /**
     * 誤った認証情報で管理者ログインできない
     */
    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        $admin = $this->createAdmin([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        // メッセージ内容を確認
        $response = $this->get('/admin/login');
        $response->assertSee('ログイン情報が登録されていません');
    }
}

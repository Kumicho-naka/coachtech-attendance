<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー（テスト用）
        User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password123'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password123'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '鈴木一郎',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password123'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }
}

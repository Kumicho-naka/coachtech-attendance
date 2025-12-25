@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
<div class="login-container">
    <h1 class="admin-login-title">管理者ログイン</h1>

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" autofocus>
            @error('email')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-input">
            @error('password')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="submit-button">管理者ログインする</button>
    </form>
</div>
@endsection
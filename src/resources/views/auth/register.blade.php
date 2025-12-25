@extends('layouts.app')

@section('title', '会員登録')

@section('content')
<div class="register-container">
    <h1 class="register-title">会員登録</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">名前</label>
            <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" autofocus>
            @error('name')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}">
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

        <div class="form-group">
            <label for="password_confirmation" class="form-label">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
        </div>

        <button type="submit" class="submit-button">登録する</button>

        <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
    </form>
</div>
@endsection
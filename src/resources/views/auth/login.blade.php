@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<div class="login-container">
    <h1 class="login-title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
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

        <button type="submit" class="submit-button">ログインする</button>

        <a href="{{ route('register') }}" class="register-link">会員登録はこちら</a>
    </form>
</div>
@endsection
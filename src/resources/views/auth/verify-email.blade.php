@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
<div class="verify-email-container">
    @if (session('status') == 'verification-link-sent')
    <div class="status-message success">
        新しい認証リンクをメールアドレスに送信しました。
    </div>
    @endif

    <p class="verify-email-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    
    <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
        @csrf
        <button type="submit" class="resend-link">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection
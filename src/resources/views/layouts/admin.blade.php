<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - 勤怠管理システム</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header class="header">
        <img src="{{ asset('images/logo.svg') }}" alt="CoachTech" class="logo">
        <nav class="nav">
            <a href="{{ route('admin.attendance.list') }}" class="nav-link">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}" class="nav-link">スタッフ一覧</a>
            <a href="{{ route('admin.stamp-correction-request.list') }}" class="nav-link">申請一覧</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-button nav-link">ログアウト</button>
            </form>
        </nav>
    </header>

    <main class="main-content">
        @if(session('message'))
        <div class="session-message">{{ session('message') }}</div>
        @endif

        @yield('content')
    </main>
</body>

</html>
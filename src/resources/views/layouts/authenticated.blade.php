<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - 勤怠管理システム</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header class="header">
        <img src="{{ asset('images/logo.svg') }}" alt="CoachTech" class="logo">
        <nav class="nav">
            <a href="{{ route('attendance.index') }}" class="nav-link">勤怠</a>
            <a href="{{ route('attendance.list') }}" class="nav-link">勤怠一覧</a>
            <a href="{{ route('stamp-correction-request.list') }}" class="nav-link">申請</a>
            <form method="POST" action="/logout" style="display: inline;">
                @csrf
                <button type="submit" class="logout-button nav-link">ログアウト</button>
            </form>
        </nav>
    </header>

    <main class="main-content">
        @yield('content')
    </main>
</body>

</html>
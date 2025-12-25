@extends('layouts.authenticated')

@section('title', '勤怠打刻')

@section('content')
<div class="attendance-container">
    <div class="status-badge">
        @if(!$todayAttendance)
        勤務外
        @elseif($todayAttendance && !$todayAttendance->end_time && !$onBreak)
        出勤中
        @elseif($onBreak)
        休憩中
        @elseif($todayAttendance && $todayAttendance->end_time)
        退勤済
        @endif
    </div>

    <div class="datetime-display">
        <p class="date" id="currentDate"></p>
        <p class="time" id="currentTime"></p>
    </div>

    <div class="button-container">
        @if(!$todayAttendance)
        <form method="POST" action="{{ route('attendance.start') }}">
            @csrf
            <button type="submit" class="action-button primary">出勤</button>
        </form>
        @elseif($todayAttendance && !$todayAttendance->end_time && !$onBreak)
        <form method="POST" action="{{ route('attendance.end') }}" style="display: inline;">
            @csrf
            <button type="submit" class="action-button primary">退勤</button>
        </form>
        <form method="POST" action="{{ route('attendance.break-start') }}" style="display: inline;">
            @csrf
            <button type="submit" class="action-button secondary">休憩入</button>
        </form>
        @elseif($onBreak)
        <form method="POST" action="{{ route('attendance.break-end') }}">
            @csrf
            <button type="submit" class="action-button primary">休憩戻</button>
        </form>
        @elseif($todayAttendance && $todayAttendance->end_time)
        <p class="finish-message">お疲れ様でした。</p>
        @endif
    </div>
</div>

<script>
    function updateDateTime() {
        const now = new Date();

        // 日付フォーマット: 2023年6月1日(木)
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const day = now.getDate();
        const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        const weekday = weekdays[now.getDay()];

        document.getElementById('currentDate').textContent =
            `${year}年${month}月${day}日(${weekday})`;

        // 時刻フォーマット: 08:00
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        document.getElementById('currentTime').textContent = `${hours}:${minutes}`;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>
@endsection
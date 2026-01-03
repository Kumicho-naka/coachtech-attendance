@extends('layouts.admin')

@section('title', '勤怠一覧')

@section('content')
<div class="admin-attendance-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>
        </div>
    </div>

    <div class="date-navigation">
        <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}" class="date-nav-link">
            <span class="nav-arrow">◀</span>
            <span class="nav-text">前日</span>
        </a>

        <div class="current-date">
            <span class="calendar-icon">📅</span>
            <span class="date-text">{{ $currentDate->format('Y/m/d') }}</span>
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}" class="date-nav-link">
            <span class="nav-text">翌日</span>
            <span class="nav-arrow">▶</span>
        </a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr class="table-header">
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allUserAttendances as $userAttendance)
                @php
                $user = $userAttendance['user'];
                $attendance = $userAttendance['attendance'];
                @endphp
                <tr class="table-row">
                    <td>{{ $user->name }}</td>
                    <td>{{ $attendance && $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    <td>{{ $attendance && $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    <td>
                        @if($attendance)
                        @php
                        $totalBreakMinutes = 0;
                        foreach($attendance->breaks as $break) {
                        if($break->start_time && $break->end_time) {
                        $start = \Carbon\Carbon::parse($break->start_time);
                        $end = \Carbon\Carbon::parse($break->end_time);
                        $totalBreakMinutes += $end->diffInMinutes($start);
                        }
                        }
                        $breakHours = floor($totalBreakMinutes / 60);
                        $breakMinutes = $totalBreakMinutes % 60;
                        @endphp
                        {{ $breakHours }}:{{ str_pad($breakMinutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    <td>
                        @if($attendance && $attendance->start_time && $attendance->end_time)
                        @php
                        $start = \Carbon\Carbon::parse($attendance->start_time);
                        $end = \Carbon\Carbon::parse($attendance->end_time);
                        $totalMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
                        $totalHours = floor($totalMinutes / 60);
                        $totalMins = $totalMinutes % 60;
                        @endphp
                        {{ $totalHours }}:{{ str_pad($totalMins, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    <td>
                        @if($attendance)
                        <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
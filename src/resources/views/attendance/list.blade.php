@extends('layouts.authenticated')

@section('title', '勤怠一覧')

@section('content')
<div class="attendance-list-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">勤怠一覧</h1>
        </div>
    </div>

    <div class="month-navigation">
        <a href="{{ route('attendance.list', ['year' => $currentDate->copy()->subMonth()->year, 'month' => $currentDate->copy()->subMonth()->month]) }}" class="month-nav-link">
            <span class="nav-arrow left">◀</span>
            <span class="nav-text">前月</span>
        </a>

        <div class="current-month">
            <span class="calendar-icon">📅</span>
            <span class="month-text">{{ $currentDate->format('Y/m') }}</span>
        </div>

        <a href="{{ route('attendance.list', ['year' => $currentDate->copy()->addMonth()->year, 'month' => $currentDate->copy()->addMonth()->month]) }}" class="month-nav-link">
            <span class="nav-text">翌月</span>
            <span class="nav-arrow right">▶</span>
        </a>
    </div>

    <div class="attendance-table-container">
        <table class="attendance-table">
            <thead>
                <tr class="table-header">
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                <tr class="table-row">
                    <td>@php
                        $date = \Carbon\Carbon::parse($attendance->date);
                        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
                        @endphp
                        {{ $date->format('m/d') }}({{ $dayOfWeek }})
                    </td>
                    <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    <td>
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
                    </td>
                    <td>
                        @if($attendance->start_time && $attendance->end_time)
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
                        <a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">勤怠データがありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
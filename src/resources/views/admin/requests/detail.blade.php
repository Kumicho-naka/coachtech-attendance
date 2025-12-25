@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('content')
<div class="detail-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">勤怠詳細</h1>
        </div>
    </div>

    <div class="detail-form">
        <div class="form-section">
            <div class="form-row">
                <label class="form-label">名前</label>
                <div class="form-value">{{ $request->user->name }}</div>
            </div>

            <div class="form-row">
                <label class="form-label">日付</label>
                <div class="form-value-group">
                    <span>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年') }}</span>
                    <span>{{ \Carbon\Carbon::parse($request->attendance->date)->format('n月j日') }}</span>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">出勤・退勤</label>
                <div class="form-value-group">
                    <span>{{ $request->start_time ? \Carbon\Carbon::parse($request->start_time)->format('H:i') : '' }}</span>
                    <span class="separator">〜</span>
                    <span>{{ $request->end_time ? \Carbon\Carbon::parse($request->end_time)->format('H:i') : '' }}</span>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">休憩</label>
                <div class="form-value-column">
                    @foreach($request->breakCorrectionRequests as $index => $break)
                    <div class="form-value-group">
                        <span>{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '' }}</span>
                        <span class="separator">〜</span>
                        <span>{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">備考</label>
                <div class="form-value">{{ $request->remarks }}</div>
            </div>
        </div>

        <div class="form-actions">
            @if($request->status === 'pending')
            <form method="POST" action="{{ route('admin.stamp-correction-request.approve.post', $request->id) }}">
                @csrf
                <button type="submit" class="submit-btn">承認</button>
            </form>
            @else
            <button class="submit-btn disabled" disabled>承認済み</button>
            @endif
        </div>
    </div>
</div>
@endsection
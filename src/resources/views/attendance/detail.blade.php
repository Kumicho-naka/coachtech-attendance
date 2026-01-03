@extends('layouts.authenticated')

@section('title', '勤怠詳細')

@section('content')
<div class="detail-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">勤怠詳細</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('attendance.detail.update', $attendance->id) }}">
        @csrf

        <div class="detail-content">
            <div class="form-section">
                <div class="form-row">
                    <label class="form-label">名前</label>
                    <div class="form-value">{{ auth()->user()->name }}</div>
                </div>

                <div class="form-row">
                    <label class="form-label">日付</label>
                    <div class="form-value-group">
                        <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">出勤・退勤</label>
                    <div class="form-value-group">
                        <input type="time" name="start_time"
                            value="{{ old('start_time', $displayData['start_time'] ? \Carbon\Carbon::parse($displayData['start_time'])->format('H:i') : '') }}"
                            class="time-input" {{ $hasPendingRequest ? 'disabled' : '' }}>
                        <span class="separator">〜</span>
                        <input type="time" name="end_time"
                            value="{{ old('end_time', $displayData['end_time'] ? \Carbon\Carbon::parse($displayData['end_time'])->format('H:i') : '') }}"
                            class="time-input" {{ $hasPendingRequest ? 'disabled' : '' }}>
                    </div>
                    <div class="error-messages">
                        @error('start_time')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('end_time')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                @foreach($breaksWithBlank as $index => $break)
                <div class="form-row">
                    <label class="form-label">休憩{{ $index > 0 ? $index + 1 : '' }}</label>
                    <div class="form-value-group">
                        @if(!$hasPendingRequest && $break)
                        <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break['id'] ?? $break->id }}">
                        @endif
                        <input type="time" name="breaks[{{ $index }}][start_time]"
                            value="{{ old('breaks.'.$index.'.start_time', $break && ($break->start_time ?? $break['start_time'] ?? null) ? \Carbon\Carbon::parse($break->start_time ?? $break['start_time'])->format('H:i') : '') }}"
                            class="time-input" {{ $hasPendingRequest ? 'disabled' : '' }}>
                        <span class="separator">〜</span>
                        <input type="time" name="breaks[{{ $index }}][end_time]"
                            value="{{ old('breaks.'.$index.'.end_time', $break && ($break->end_time ?? $break['end_time'] ?? null) ? \Carbon\Carbon::parse($break->end_time ?? $break['end_time'])->format('H:i') : '') }}"
                            class="time-input" {{ $hasPendingRequest ? 'disabled' : '' }}>
                    </div>
                    <div class="error-messages">
                        @error('breaks.'.$index.'.start_time')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('breaks.'.$index.'.end_time')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                @endforeach

                <div class="form-row">
                    <label class="form-label">備考</label>
                    <textarea name="remarks" class="remarks-input" {{ $hasPendingRequest ? 'disabled' : '' }}>{{ old('remarks', $displayData['remarks']) }}</textarea>
                    @error('remarks')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        @if(!$hasPendingRequest)
        <div class="form-actions">
            <button type="submit" class="submit-btn">修正</button>
        </div>
        @endif
    </form>

    @if($hasPendingRequest)
    <div class="pending-warning">*承認待ちのため修正はできません。</div>
    @endif
</div>
@endsection
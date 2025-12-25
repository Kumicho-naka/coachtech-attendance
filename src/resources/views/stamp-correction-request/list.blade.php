@extends('layouts.authenticated')

@section('title', '申請一覧')

@section('content')
<div class="request-list-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">申請一覧</h1>
        </div>
    </div>

    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('pending')">承認待ち</button>
        <button class="tab-btn" onclick="showTab('approved')">承認済み</button>
    </div>

    <div id="pending-tab" class="tab-content active">
        <div class="request-table-container">
            <table class="request-table">
                <thead>
                    <tr class="table-header">
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $request)
                    <tr class="table-row">
                        <td>承認待ち</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ Str::limit($request->remarks, 10) }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td><a href="{{ route('attendance.detail', $request->attendance_id) }}" class="detail-link">詳細</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">承認待ちの申請はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="approved-tab" class="tab-content">
        <div class="request-table-container">
            <table class="request-table">
                <thead>
                    <tr class="table-header">
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $request)
                    <tr class="table-row">
                        <td>承認済み</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ Str::limit($request->remarks, 10) }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td><a href="{{ route('attendance.detail', $request->attendance_id) }}" class="detail-link">詳細</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">承認済みの申請はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function showTab(tabName) {
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(tabName + '-tab').classList.add('active');
    }
</script>
@endsection
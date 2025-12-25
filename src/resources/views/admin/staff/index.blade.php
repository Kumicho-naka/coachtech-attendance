@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('content')
<div class="staff-list-container">
    <div class="page-header">
        <div class="page-title-wrapper">
            <div class="title-border"></div>
            <h1 class="page-title">スタッフ一覧</h1>
        </div>
    </div>

    <div class="staff-table-container">
        <table class="staff-table">
            <thead>
                <tr class="table-header">
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="table-row">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', $user->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px;">スタッフがいません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
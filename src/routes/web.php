<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceListController as AdminAttendanceListController;
use App\Http\Controllers\Admin\AttendanceDetailController as AdminAttendanceDetailController;
use App\Http\Controllers\Admin\StaffListController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use App\Http\Controllers\Admin\StampCorrectionApprovalController;
use App\Http\Controllers\Admin\AdminLoginController;

// 一般ユーザー用ルート（Fortifyが/register, /login, /logoutを提供）
Route::middleware(['auth'])->group(function () {
    // 勤怠打刻
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // 勤怠一覧・詳細
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'update'])->name('attendance.detail.update');

    // 修正申請一覧
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp-correction-request.list');
});

// 管理者用ルート
Route::prefix('admin')->name('admin.')->group(function () {
    // 管理者ログイン（Fortifyパターン + FormRequest）
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store']);
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::middleware(['auth'])->group(function () {
        // 勤怠一覧・詳細
        Route::get('/attendance/list', [AdminAttendanceListController::class, 'index'])->name('attendance.list');
        Route::get('/attendance/{id}', [AdminAttendanceDetailController::class, 'show'])->name('attendance.detail');
        Route::put('/attendance/{id}', [AdminAttendanceDetailController::class, 'update'])->name('attendance.update');

        // スタッフ一覧・スタッフ別勤怠
        Route::get('/staff/list', [StaffListController::class, 'index'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [StaffAttendanceController::class, 'show'])->name('attendance.staff');
        Route::post('/attendance/staff/{id}/csv', [StaffAttendanceController::class, 'exportCsv'])->name('attendance.staff.csv');

        // 修正申請一覧・承認
        Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])->name('stamp-correction-request.list');
        Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionApprovalController::class, 'show'])->name('stamp-correction-request.approve');
        Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionApprovalController::class, 'approve'])->name('stamp-correction-request.approve.post');
    });
});

Route::get('/', function () {
    return redirect('/login');
});

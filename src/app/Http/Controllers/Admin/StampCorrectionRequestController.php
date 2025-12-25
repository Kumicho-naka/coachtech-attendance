<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $pendingRequests = AttendanceCorrectionRequest::where('status', 'pending')
            ->with(['user', 'attendance', 'breakCorrectionRequests'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedRequests = AttendanceCorrectionRequest::where('status', 'approved')
            ->with(['user', 'attendance', 'breakCorrectionRequests'])
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('admin.requests.index', compact('pendingRequests', 'approvedRequests'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pendingRequests = AttendanceCorrectionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['attendance', 'breakCorrectionRequests'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedRequests = AttendanceCorrectionRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->with(['attendance', 'breakCorrectionRequests'])
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('stamp-correction-request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
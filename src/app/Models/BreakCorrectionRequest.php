<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'break_id',
        'start_time',
        'end_time',
    ];

    public function attendanceCorrectionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }

    public function restBreak()
    {
        return $this->belongsTo(RestBreak::class, 'break_id');
    }
}
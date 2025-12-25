<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestBreak extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakCorrectionRequests()
    {
        return $this->hasMany(BreakCorrectionRequest::class, 'break_id');
    }
}
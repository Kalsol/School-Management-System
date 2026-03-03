<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class TeachersAttendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'teacher_id',
        'session_id',
        'attendance_date',
        'time_in',
        'minutes_late',
        'status'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function staffRecord()
    {
        return $this->belongsTo(StaffRecord::class, 'teacher_id', 'user_id');
    }
}

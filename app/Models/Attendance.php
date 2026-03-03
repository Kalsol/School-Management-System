<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'section_id', 'session_id', 
        'attendance_date', 'time_in', 'session_actual_start', 
        'minutes_late', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function student_record()
    {
        return $this->belongsTo(StudentRecord::class, 'student_id', 'user_id');
    }
}
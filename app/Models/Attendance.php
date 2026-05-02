<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'session_id',
        'attendance_date',
        'time_in',
        'minutes_late',
        'status',
        'remarks',
        'is_excused',
        'excuse_type',
        'evidence',
        'admin_response',
        'admin_id',
        'handled_at'
    ];

    protected $casts = [
    'handled_at' => 'datetime',
    'is_excused' => 'boolean',
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
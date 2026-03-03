<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbsenteeNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $student;
    public $date;

    public function __construct($student, $date)
    {
        $this->student = $student;
        $this->date = $date;
    }

    public function build()
    {
        return $this->subject('Attendance Alert: Absence Reported')
                    ->markdown('emails.attendance.absent');
    }
}

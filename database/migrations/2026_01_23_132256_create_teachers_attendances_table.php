<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->string('session_id')->nullable(); // Academic Year (e.g., 2025/2026)
            $table->date('attendance_date');          // The Date sent by Python
            $table->time('time_in');                 // Arrival Time
            $table->integer('minutes_late')->default(0);
            $table->enum('status', ['Present', 'Late', 'Absent'])->default('Present');
            $table->timestamps();
            
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('user_id')->on('staff_records')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers_attendances');
    }
}

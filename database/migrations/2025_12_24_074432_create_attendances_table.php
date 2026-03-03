<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            
            $table->string('session_id')->nullable(); // Academic Year (e.g., 2025/2026)
            $table->date('attendance_date');          // The Date sent by Python
            $table->time('time_in');                 // Arrival Time
            $table->integer('minutes_late')->default(0);
            $table->enum('status', ['Present', 'Late', 'Absent'])->default('Present');
            
            $table->text('remarks')->nullable(); // Parent's reason
            $table->boolean('is_excused')->default(false); // Approved by admin
            $table->timestamps();
            
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down() { Schema::dropIfExists('attendances'); }
}
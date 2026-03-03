<?php

use Illuminate\Http\Request;
use App\Http\Controllers\SupportTeam\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Ensure this is NOT wrapped in 'auth:sanctum' for now while testing
Route::get('/student-map', [AttendanceController::class, 'getStudentMap']);
Route::post('/mark-student-attendance', [AttendanceController::class, 'markStudentByFace']);

Route::get('/teacher-map', [AttendanceController::class, 'getTeacherMap']);
Route::post('/mark-teacher-attendance', [AttendanceController::class, 'markTeacherByFace']);

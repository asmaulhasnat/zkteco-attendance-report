<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Route\DB;
use App\Http\Controllers\AttendanceReportController;


Route::get('/',[AttendanceReportController::class,'index']);
Route::post('/',[AttendanceReportController::class,'monthlyAttendanceReport']);
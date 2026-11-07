<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/shift-templates', [ShiftTemplateController::class, 'index']);
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
Route::patch('/schedules/{schedule}', [ScheduleController::class, 'update']);
Route::get('/schedules/{schedule}/shifts', [ShiftController::class, 'index']);
Route::post('/schedules/{schedule}/shifts', [ShiftController::class, 'store']);
Route::delete('/schedules/{schedule}/shifts', [ShiftController::class, 'destroyMany']);
Route::patch('/shifts/{shift}', [ShiftController::class, 'update']);
Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy']);

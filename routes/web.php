<?php

use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SubjectsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminsController;

// Route::get('/', [AdminsController::class, 'index']);

Route::get('/subject', [SubjectsController::class, 'index']);
Route::get('/doctor', [DoctorsController::class, 'index']);
Route::get('/students', [StudentsController::class, 'index']);
// Filteration on Students
// Route::get('/students?specialization=CS', [StudentsController::class, 'index']);
// Route::get('/students?level=One', [StudentsController::class, 'index']);
// Route::get('/students?level=One&specialization=CS', [StudentsController::class, 'index']);
Route::get('/admin', [AdminsController::class, 'index']);
Route::get('/show', [AdminsController::class, 'showGrade']);

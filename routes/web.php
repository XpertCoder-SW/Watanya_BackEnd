<?php

use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SubjectsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminsController;
use App\Http\Controllers\GradesController;

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




Route::prefix('student')->group(function () {
    Route::post('/store', [StudentsController::class, 'store']);
    Route::put('/update/{id}', [StudentsController::class, 'update']);
});

Route::prefix('doctors')->group(function () {
    Route::post('/store' , [DoctorsController::class, 'store']);
    Route::put('/update/{id}' , [DoctorsController::class, 'updte']);
});

Route::prefix('subjects')->group(function () {
    Route::post('/store', [SubjectsController::class, 'store']);
    Route::put('/update/{id}', [SubjectsController::class, 'update']);
    Route::put('/updateSubjectGrade/{subjectId}', [SubjectsController::class, 'updateSubjectGrade']);
});

Route::prefix('grades')->group(function(){
    Route::post('/store',[GradesController::class,'store']);
});

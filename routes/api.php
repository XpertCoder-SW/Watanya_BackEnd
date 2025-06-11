<?php

use Illuminate\Http\Request;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\SubjectsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminsController;
use App\Http\Controllers\Auth\Controller;


///////////////////////////////////////////////////////////// Start of Admin Setting APIs ///////////////////////////////////////////////////////////

Route::post('/setting', [AdminsController::class, 'store']); // Create new admin settings
Route::get('/setting', [AdminsController::class, 'index']); // Get all admin settings
Route::put('/setting/{id}', [AdminsController::class, 'update']); // Update admin settings by ID
Route::delete('/setting/{id}', [AdminsController::class, 'delete']); // Delete admin settings by ID

///////////////////////////////////////////////////////////// End of Admin Setting APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Subjects APIs ///////////////////////////////////////////////////////////

Route::post('/admin/subjects', [SubjectsController::class, 'store']); // Create new subject
Route::get('/admin/subjects', [SubjectsController::class, 'index']); // Get all subjects
Route::get('/admin/subjects/{id}', [SubjectsController::class, 'show']); // Get subject by ID
Route::put('/admin/subjects/{id}', [SubjectsController::class, 'update']); // Update subject by ID
Route::delete('/admin/subjects/{id}', [SubjectsController::class, 'delete']); // Delete subject by ID


///////////////////////////////////////////////////////////// End of Admin Subjects APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Doctors APIs ///////////////////////////////////////////////////////////

Route::post('/admin/doctors', [DoctorsController::class, 'store']); // Create new Doctor
Route::get('/admin/doctors', [DoctorsController::class, 'index']); // Get all Doctors
Route::get('/admin/doctors/{id}', [DoctorsController::class, 'show']); // Get Doctor by ID
Route::put('/admin/doctors/{id}', [DoctorsController::class, 'update']); // Update Doctor by ID
Route::delete('/admin/doctors/{id}', [DoctorsController::class, 'delete']); // delete Doctor by ID

///////////////////////////////////////////////////////////// End of Admin Doctors APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Students APIs ///////////////////////////////////////////////////////////

Route::post('/admin/students', [StudentsController::class, 'store']); // Create new student
Route::get('/admin/students', [StudentsController::class, 'index']); // Get all student
Route::get('/admin/students/{id}', [StudentsController::class, 'show']); // Get student by ID
Route::put('/admin/students/{id}', [StudentsController::class, 'update']); // Update student by ID
Route::delete('/admin/students/{id}', [StudentsController::class, 'delete']); // Delete student by ID

///////////////////////////////////////////////////////////// End of Admin Students APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Doctor Subject APIs ///////////////////////////////////////////////////////////

Route::get('/doctor/{doctor_id}/subjects', [SubjectsController::class, 'getDoctorSubjects']); // Update subject grades by ID
Route::put('/doctor/subject/{subject_id}', [SubjectsController::class, 'updateSubjectGrades']); // Update subject grades by ID

///////////////////////////////////////////////////////////// End of Doctor Subject APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Student Grade APIs ///////////////////////////////////////////////////////////

Route::post('/student/{student_id}/grades', [GradesController::class, 'store']); // Create new subject
Route::get('/doctor/{doctor_id}/assigned-students', [SubjectsController::class, 'getAssignedStudents']);
Route::get('/student/{student_id}/grades/{subject_id}', [GradesController::class, 'show']);
Route::put('/student/{student_id}/grades/{subject_id}', [GradesController::class, 'update']);

///////////////////////////////////////////////////////////// End ofStudent Grade APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Student APIs ///////////////////////////////////////////////////////////

Route::get('/student/{student_id}/grades-gpa', [GradesController::class, 'studentGradesWithGpa']);

///////////////////////////////////////////////////////////// End of Student APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Login APIs ///////////////////////////////////////////////////////////

Route::post('/login', [Controller::class, 'login']);

///////////////////////////////////////////////////////////// End of Login APIs ///////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////// Start of Satatistics APIs ///////////////////////////////////////////////////////////

Route::get('/admin/students/gpa-stats', [StudentsController::class, 'getGpaStats']);
Route::get('/subject/{subject_id}/statistics', [SubjectsController::class, 'getSubjectStatistics']);
Route::get('/student/{student_id}/details', [SubjectsController::class, 'getStudentDetails']);
Route::get('/api/admin/students/{student_id}/examination-results', [StudentsController::class, 'getExaminationResults']);
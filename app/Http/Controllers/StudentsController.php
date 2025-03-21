<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentsController 
{
    // Get All Students to be shown in the first page of Students with filteration
    public function index(Request $request)
    {
        $query = Student::query();
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        if ($request->has('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        $students = $query->get(['name', 'email', 'phoneNumber', 'level', 'specialization']);
        return response()->json(['students' => $students], 200);
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:students,code',
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:students,email',
            'phoneNumber' => 'required|string|max:100',
            'level' => 'required|in:One,Two,Three,Four',
            'specialization' => 'required|in:CS,IT',
        ]);

        $student = Student::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phoneNumber' => $validated['phoneNumber'],
            'level' => $validated['level'],
            'specialization' => $validated['specialization'],
            'password' => Hash::make($validated['code']), // The default password is code.
            'academic_year' => '2023-2024', // It can be dynamic
            'gpa' => 0.00, // Default value because the student is new and has not received grades yet.
        ]);

        return response()->json([
            "message" => "Student added successfully",
            "student" => "$student",
        ], 200);
    }



    public function update(Request $request , $id){

    }
}

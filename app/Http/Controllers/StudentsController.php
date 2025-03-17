<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

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
}

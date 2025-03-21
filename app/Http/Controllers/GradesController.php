<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class GradesController 
{





    public function store(Request $request)
    {

        $studentDataValidated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|exists:watnya_students,code',
            'level' => 'required|in:One,Two,Three,Four',
            'specialization' => 'required|in:CS,IT',
        ]);

        // Verify grade data 
        $gradeDataValidated = $request->validate([
            'grade' => 'required|exists:watnya_subjects,id',
            'midtermGrade' => 'required|numeric|min:0|max:100',
            'practicalGrade' => 'required|numeric|min:0|max:100',
            'yearsWorkGrade' => 'required|numeric|min:0|max:100',
            'finalGrade' => 'required|numeric|min:0|max:100',
        ]);

        // Retrieve student based on code only
        $student = Student::where('code', $studentDataValidated['code'])->firstOrFail();

        // Retrieve material based on grade (subject_id) only
        $subject = Subject::findOrFail($gradeDataValidated['grade']);

        // Calculate the overall score
        $totalGrade = $gradeDataValidated['midtermGrade'] +
            $gradeDataValidated['practicalGrade'] +
            $gradeDataValidated['yearsWorkGrade'] +
            $gradeDataValidated['finalGrade'];


        // Create a score record
        $grade = Grade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'totalGrade' => $totalGrade,
            'totalGradeChar' => $this->convertToGradeChar($totalGrade),
            'midtermGrade' => $gradeDataValidated['midtermGrade'],
            'practicalGrade' => $gradeDataValidated['practicalGrade'],
            'yearsWorkGrade' => $gradeDataValidated['yearsWorkGrade'],
            'finalGrade' => $gradeDataValidated['finalGrade'],
        ]);

        return response()->json([
            'message' => 'Student grade added successfully',
            'grade' => $grade,
        ], 201);
    }

    private function convertToGradeChar($totalGrade)
    {
        if ($totalGrade >= 90) return 'A';
        if ($totalGrade >= 80) return 'B';
        if ($totalGrade >= 70) return 'C';
        if ($totalGrade >= 60) return 'D';
        return 'F';
    }
}

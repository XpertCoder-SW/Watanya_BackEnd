<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectsController 
{
    // Get All Subject to be shown in the first page of subjects
    public function index()
    {
        $subjects = Subject::all(['code', 'name', 'creditHours', 'specialization', 'level', 'semester']);
        return response()->json(['subjects' => $subjects], 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:watnya_subjects,code',
            'name' => 'required|string|max:255',
            'creditHours' => 'required|integer|min:1',
            'specialization' => 'required|in:CS,IT',
            'level' => 'required|in:One,Two,Three,Four',
            'semester' => 'required|in:One,Two',
        ]);

        $subject = Subject::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'creditHours' => $validated['creditHours'],
            'specialization' => $validated['specialization'],
            'level' => $validated['level'],
            'semester' => $validated['semester'],
        ]);

        return response()->json([
            "message" => "Subject added successfully",
            "subject" => $subject,
        ], 201);
    }

       
    public function updte(Request $request , $id){

    }







    // ///////////////////////////

    public function updateSubjectGrade(Request $request, $subjectId)
    {
        // Verify main data
        $subjectValidated = $request->validate([
            'code' => 'required|string|max:255|unique:subjects,code,' . $subjectId,
            'name' => 'required|string|max:255',
            'creditHours' => 'required|integer|min:1',
            'specialization' => 'required|in:CS,IT',
            'level' => 'required|in:One,Two,Three,Four',
            'semester' => 'required|in:One,Two',
        ]);

        $gradeDistributionValidated = $request->validate([
            'totalGrade' => 'required|numeric|min:0|max:100',
            'midtermGrade' => 'required|numeric|min:0|max:100',
            'practicalGrade' => 'required|numeric|min:0|max:100',
            'yearsWorkGrade' => 'required|numeric|min:0|max:100',
            'finalGrade' => 'required|numeric|min:0|max:100',
        ]);


        $totalGrade = $gradeDistributionValidated['midtermGrade'] + $gradeDistributionValidated['practicalGrade'] +
            $gradeDistributionValidated['yearsWorkGrade'] + $gradeDistributionValidated['finalGrade'];

        // Update subject data with grade distribution
        $subject = Subject::findOrFail($subjectId);
        $subject->update([
            'code' => $subjectValidated['code'],
            'name' => $subjectValidated['name'],
            'creditHours' => $subjectValidated['creditHours'],
            'specialization' => $subjectValidated['specialization'],
            'level' => $subjectValidated['level'],
            'semester' => $subjectValidated['semester'],
            'totalGrde' => $totalGrade,
            'midtermGrade' => $gradeDistributionValidated['midtermGrade'],
            'practicalGrade' => $gradeDistributionValidated['practicalGrade'],
            'yearsWorkGrade' => $gradeDistributionValidated['yearsWorkGrade'],
            'finalGrade' => $gradeDistributionValidated['finalGrade'],
        ]);

        return response()->json([
            'message' => 'Subject and grade data updated successfully',
            'subject' => $subject,

        ], 200);
    }
}

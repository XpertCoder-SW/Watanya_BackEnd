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
            'code' => 'required|string|max:255|unique:subjects,code',
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


    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            "code" => "sometimes|string|max:255|unique:subjects,code,$id",
            "name" => "sometimes|string|max:255",
            "creditHours" => "sometimes|integre|min:1",
            "specialization" => "sometimes|in:CS,IT",
            "level" => "sometimes|in:One,Two,Three,Four",
            "semester" => "sometimes|in:One,Two",
        ]);


        $updateData = [];

        if (isset($validated['code'])) {
            $updateData['code'] = $validated['code'];
        }

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['creditHours'])) {
            $updateData['creditHours'] = $validated['creditHours'];
        }

        if (isset($validated['specialization'])) {
            $updateData['specialization'] = $validated['specialization'];
        }

        if (isset($validated['level'])) {
            $updateData['level'] = $validated['level'];
        }

        if (isset($validated['semester'])) {
            $updateData['semester'] = $validated['semester'];
        }


        if (empty($updateData)) {
            return response()->json([
                "message" => "At least one field must be provided to update the subject."
            ], 422);
        }


        $subject = Subject::where("id", "$id")->update($updateData);


        if (!$subject) {
            return response()->json([
                "message" => "Subject not found."
            ], 404);
        }


        $updatedSubject = Subject::find($id);


        return response()->json([
            "message" => "Subject Updated Successfully",
            "subject" => $updatedSubject,
        ], 201);
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

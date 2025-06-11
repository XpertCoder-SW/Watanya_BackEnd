<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Admin;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Doctor Student",
 * )
 * @OA\Tag(
 *     name="Student",
 * )
 */
class GradesController
{
    /**
     * @OA\Post(
     *     path="/api/student/{student_id}/grades",
     *     tags={"Doctor Student"},
     *     summary="Add student grades",
     *     description="Store student grade information in grades table for specific subject",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject_id", "midtermGrade", "practicalGrade", "yearsWorkGrade", "finalGrade", "gradeStatus"},
     *             @OA\Property(property="subject_id", type="integer", description="Subject ID", example=1),
     *             @OA\Property(property="midtermGrade", type="number", format="float", minimum=0, maximum=100, example=25),
     *             @OA\Property(property="practicalGrade", type="number", format="float", minimum=0, maximum=100, example=15),
     *             @OA\Property(property="yearsWorkGrade", type="number", format="float", minimum=0, maximum=100, example=20),
     *             @OA\Property(property="finalGrade", type="number", format="float", minimum=0, maximum=100, example=40),
     *             @OA\Property(property="gradeStatus", type="string", enum={"pass", "i", "i*", "ff*", "others"}, example="pass")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade created successfully in grades table",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="grade",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=1),
     *                 @OA\Property(property="subject_id", type="integer", example=1),
     *                 @OA\Property(property="totalGrade", type="number", format="float", example=100),
     *                 @OA\Property(property="totalGradeChar", type="string", example="A"),
     *                 @OA\Property(property="midtermGrade", type="number", format="float", example=25),
     *                 @OA\Property(property="practicalGrade", type="number", format="float", example=15),
     *                 @OA\Property(property="yearsWorkGrade", type="number", format="float", example=20),
     *                 @OA\Property(property="finalGrade", type="number", format="float", example=40),
     *                 @OA\Property(property="gradeStatus", type="string", example="pass"),
     *                 @OA\Property(property="academic_year", type="string", example="2023-2024")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Grade already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Grade already exists for this student in the specified subject"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="subject_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="This student already has a grade record for this subject")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="subject_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected subject_id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student or subject not found")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $student_id)
    {
        // Validate the request data including gradeStatus
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'midtermGrade' => 'nullable|numeric|min:0|max:100',
            'practicalGrade' => 'nullable|numeric|min:0|max:100',
            'yearsWorkGrade' => 'nullable|numeric|min:0|max:100',
            'finalGrade' => 'nullable|numeric|min:0|max:100',
            'gradeStatus' => 'required|in:pass,i,i*,ff*,others',
        ]);

        // Check if grade already exists for this student and subject
        $existingGrade = Grade::where('student_id', $student_id)
                            ->where('subject_id', $validated['subject_id'])
                            ->first();

        if ($existingGrade) {
            return response()->json([
                'message' => 'Grade already exists for this student in the specified subject',
                'errors' => [
                    'subject_id' => ['This student already has a grade record for this subject']
                ]
            ], 409);
        }

        // Find student and subject
        $student = Student::findOrFail($student_id);
        $subject = Subject::findOrFail($validated['subject_id']);

        // Compare grades with subject maximum grades
        if (($validated['midtermGrade'] ?? 0) > $subject->midtermGrade) {
            return response()->json([
                'message' => 'Midterm grade exceeds maximum allowed for this subject',
                'errors' => [
                    'midtermGrade' => ['The midterm grade cannot exceed ' . $subject->midtermGrade]
                ]
            ], 422);
        }

        if (($validated['practicalGrade'] ?? 0) > $subject->practicalGrade) {
            return response()->json([
                'message' => 'Practical grade exceeds maximum allowed for this subject',
                'errors' => [
                    'practicalGrade' => ['The practical grade cannot exceed ' . $subject->practicalGrade]
                ]
            ], 422);
        }

        if (($validated['yearsWorkGrade'] ?? 0) > $subject->yearsWorkGrade) {
            return response()->json([
                'message' => 'Years work grade exceeds maximum allowed for this subject',
                'errors' => [
                    'yearsWorkGrade' => ['The years work grade cannot exceed ' . $subject->yearsWorkGrade]
                ]
            ], 422);
        }

        if (($validated['finalGrade'] ?? 0) > $subject->finalGrade) {
            return response()->json([
                'message' => 'Final grade exceeds maximum allowed for this subject',
                'errors' => [
                    'finalGrade' => ['The final grade cannot exceed ' . $subject->finalGrade]
                ]
            ], 422);
        }

        // Calculate total grade
        $totalGrade = ($validated['midtermGrade'] ?? 0) +
                      ($validated['practicalGrade'] ?? 0) +
                      ($validated['yearsWorkGrade'] ?? 0) +
                      ($validated['finalGrade'] ?? 0);

        // Validate total grade doesn't exceed subject's maximum total grade if exists
        if ($subject->totalGrade && $totalGrade > $subject->totalGrade) {
            return response()->json([
                'message' => 'Total grade exceeds maximum allowed for this subject',
                'errors' => [
                    'total' => ['The total grade cannot exceed ' . $subject->totalGrade]
                ]
            ], 422);
        }

        // Get the academic year from Admin table
        $admin = Admin::first();
        $academicYear = $admin ? $admin->academic_year : date('Y').'-'.(date('Y')+1);

        // Create grade record in grades table
        $gradeData = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'totalGrade' => $totalGrade,
            'totalGradeChar' => $this->convertToGradeChar($totalGrade),
            'midtermGrade' => $validated['midtermGrade'] ?? 0,
            'practicalGrade' => $validated['practicalGrade'] ?? 0,
            'yearsWorkGrade' => $validated['yearsWorkGrade'] ?? 0,
            'finalGrade' => $validated['finalGrade'] ?? 0,
            'gradeStatus' => $validated['gradeStatus'],
            'academic_year' => $academicYear,
        ];

        $grade = Grade::create($gradeData);

        // Remove timestamps from the response
        $responseData = [
            'grade' => collect($grade)->except(['created_at', 'updated_at'])->toArray()
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Convert numeric grade to letter grade
     *
     * @param float $totalGrade
     * @return string
     */
    private function convertToGradeChar($totalGrade)
    {
        if ($totalGrade >= 80) return 'A';
        if ($totalGrade >= 75) return 'B+';
        if ($totalGrade >= 65) return 'B';
        if ($totalGrade >= 60) return 'C+';
        if ($totalGrade >= 50) return
     'C';
        return 'F';
    }

    /**
     * @OA\Get(
     *     path="/api/student/{student_id}/grades/{subject_id}",
     *     tags={"Doctor Student"},
     *     summary="Get student grade for a subject",
     *     description="Retrieve a student's grade record for a specific subject",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="path",
     *         description="ID of the subject",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="grade", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Grade not found")
     *         )
     *     )
     * )
     */
    public function show($student_id, $subject_id)
    {
        $grade = Grade::where('student_id', $student_id)
                      ->where('subject_id', $subject_id)
                      ->first();

        if (!$grade) {
            return response()->json([
                'message' => 'Grade not found'
            ], 404);
        }

        $responseData = [
            'grade' => collect($grade)->except(['created_at', 'updated_at'])->toArray()
        ];

        return response()->json($responseData, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/student/{student_id}/grades/{subject_id}",
     *     tags={"Doctor Student"},
     *     summary="Update student grade for a subject",
     *     description="Update a student's grade record for a specific subject",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="path",
     *         description="ID of the subject",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"midtermGrade", "practicalGrade", "yearsWorkGrade", "finalGrade", "gradeStatus"},
     *             @OA\Property(property="midtermGrade", type="number", format="float", minimum=0, maximum=100, example=25),
     *             @OA\Property(property="practicalGrade", type="number", format="float", minimum=0, maximum=100, example=15),
     *             @OA\Property(property="yearsWorkGrade", type="number", format="float", minimum=0, maximum=100, example=20),
     *             @OA\Property(property="finalGrade", type="number", format="float", minimum=0, maximum=100, example=40),
     *             @OA\Property(property="gradeStatus", type="string", enum={"pass", "i", "i*", "ff*", "others"}, example="pass")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="grade", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Grade not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $student_id, $subject_id)
    {
        $grade = Grade::where('student_id', $student_id)
                      ->where('subject_id', $subject_id)
                      ->first();

        if (!$grade) {
            return response()->json([
                'message' => 'Grade not found'
            ], 404);
        }

        $validated = $request->validate([
            'midtermGrade' => 'sometimes|nullable|numeric|min:0|max:100',
            'practicalGrade' => 'sometimes|nullable|numeric|min:0|max:100',
            'yearsWorkGrade' => 'sometimes|nullable|numeric|min:0|max:100',
            'finalGrade' => 'sometimes|nullable|numeric|min:0|max:100',
            'gradeStatus' => 'required|in:pass,i,i*,ff*,others',
        ]);

        $subject = Subject::findOrFail($subject_id);

        if (($validated['midtermGrade'] ?? 0) > $subject->midtermGrade) {
            return response()->json([
                'message' => 'Midterm grade exceeds maximum allowed for this subject',
                'errors' => [
                    'midtermGrade' => ['The midterm grade cannot exceed ' . $subject->midtermGrade]
                ]
            ], 422);
        }

        if (($validated['practicalGrade'] ?? 0) > $subject->practicalGrade) {
            return response()->json([
                'message' => 'Practical grade exceeds maximum allowed for this subject',
                'errors' => [
                    'practicalGrade' => ['The practical grade cannot exceed ' . $subject->practicalGrade]
                ]
            ], 422);
        }

        if (($validated['yearsWorkGrade'] ?? 0) > $subject->yearsWorkGrade) {
            return response()->json([
                'message' => 'Years work grade exceeds maximum allowed for this subject',
                'errors' => [
                    'yearsWorkGrade' => ['The years work grade cannot exceed ' . $subject->yearsWorkGrade]
                ]
            ], 422);
        }

        if (($validated['finalGrade'] ?? 0) > $subject->finalGrade) {
            return response()->json([
                'message' => 'Final grade exceeds maximum allowed for this subject',
                'errors' => [
                    'finalGrade' => ['The final grade cannot exceed ' . $subject->finalGrade]
                ]
            ], 422);
        }

        $totalGrade = ($validated['midtermGrade'] ?? 0) +
                      ($validated['practicalGrade'] ?? 0) +
                      ($validated['yearsWorkGrade'] ?? 0) +
                      ($validated['finalGrade'] ?? 0);

        if ($subject->totalGrade && $totalGrade > $subject->totalGrade) {
            return response()->json([
                'message' => 'Total grade exceeds maximum allowed for this subject',
                'errors' => [
                    'total' => ['The total grade cannot exceed ' . $subject->totalGrade]
                ]
            ], 422);
        }

        $admin = Admin::first();
        $academicYear = $admin ? $admin->academic_year : date('Y').'-'.(date('Y')+1);

        $grade->update([
            'midtermGrade' => $validated['midtermGrade'] ?? 0,
            'practicalGrade' => $validated['practicalGrade'] ?? 0,
            'yearsWorkGrade' => $validated['yearsWorkGrade'] ?? 0,
            'finalGrade' => $validated['finalGrade'] ?? 0,
            'totalGrade' => $totalGrade,
            'totalGradeChar' => $this->convertToGradeChar($totalGrade),
            'gradeStatus' => $validated['gradeStatus'],
            'academic_year' => $academicYear,
        ]);

        $responseData = [
            'grade' => collect($grade)->except(['created_at', 'updated_at'])->toArray()
        ];

        return response()->json($responseData, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/student/{student_id}/grades-gpa",
     *     tags={"Student"},
     *     summary="Get grades for a student for the current academic year and semester",
     *     description="Returns only the grades for the academic year and semester specified in the Admin table, and only for subjects that belong to that semester. For example, if the Admin table is set to Semester 2, only grades for subjects in Semester 2 will be shown. If showGrades in Admin is false, returns an empty array and GPA/CGPA = 0.",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades, GPA, and CGPA retrieved successfully. If showGrades is false, grades will be an empty array and GPA/CGPA will be 0.",
     *         @OA\JsonContent(
     *             @OA\Property(property="grades", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="subject_id", type="integer"),
     *                 @OA\Property(property="subject_name", type="string", example="Mathematics"),
     *                 @OA\Property(property="credit_hours", type="integer", example=3),
     *                 @OA\Property(property="totalGrade", type="number"),
     *                 @OA\Property(property="totalGradeChar", type="string"),
     *                 @OA\Property(property="midtermGrade", type="number"),
     *                 @OA\Property(property="practicalGrade", type="number"),
     *                 @OA\Property(property="yearsWorkGrade", type="number"),
     *                 @OA\Property(property="finalGrade", type="number"),
     *                 @OA\Property(property="gradeStatus", type="string"),
     *                 @OA\Property(property="academic_year", type="string"),
     *                 @OA\Property(property="current_semester", type="string")
     *             )),
     *             @OA\Property(property="gpa", type="number", format="float"),
     *             @OA\Property(property="cgpa", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
     *         )
     *     )
     * )
     */

     public function studentGradesWithGpa($student_id)
    {
        $student = Student::find($student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $admin = Admin::first();
        $currentAcademicYear = $admin ? $admin->academic_year : date('Y').'-'.(date('Y')+1);
        $currentSemester = $admin ? $admin->current_semester : null;
        $showGrades = $admin ? $admin->showGrades : true;
        
        if (!$showGrades) {
            return response()->json([
                'grades' => [],
                'gpa' => 0,
                'cgpa' => 0
            ], 200);
        }

        $subjectIds = [];
        if ($currentSemester !== null) {
            $subjectIds = Subject::where('semester', $currentSemester)->pluck('id')->toArray();
        }

        $grades = Grade::where('student_id', $student_id)
            ->where('academic_year', $currentAcademicYear)
            ->when($currentSemester !== null, function($query) use ($subjectIds) {
                return $query->whereIn('subject_id', $subjectIds);
            })
            ->get();

        if ($grades->isEmpty()) {
            return response()->json([
                'grades' => [],
                'gpa' => 0,
                'cgpa' => 0
            ], 200);
        }

        // GPA/CGPA calculation formula
        $calculateGpa = function($gradesCollection) {
            $totalPoints = 0;
            $totalCredits = 0;

            foreach ($gradesCollection as $grade) {
                $subject = Subject::find($grade->subject_id);
                if (!$subject || !$subject->creditHours) continue;

                $points = match($grade->totalGradeChar) {
                    'A' => 4.0,
                    'B+' => 3.5,
                    'B' => 3.0,
                    'C+' => 2.5,
                    'C' => 2.0,
                    default => 0.0,
                };

                $totalPoints += $points * $subject->creditHours;
                $totalCredits += $subject->creditHours;
            }

            return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
        };

        $gpa = $calculateGpa($grades);
        $cgpa = $gpa;

        // Prepare grades with subject name and credit hours
        $gradesWithSubject = $grades->map(function($grade) {
            $subject = Subject::find($grade->subject_id);
            return [
                'id' => $grade->id,
                'subject_id' => $grade->subject_id,
                'subject_name' => $subject ? $subject->name : null,
                'credit_hours' => $subject ? $subject->creditHours : null,
                'totalGrade' => $grade->totalGrade,
                'totalGradeChar' => $grade->totalGradeChar,
                'midtermGrade' => $grade->midtermGrade,
                'practicalGrade' => $grade->practicalGrade,
                'yearsWorkGrade' => $grade->yearsWorkGrade,
                'finalGrade' => $grade->finalGrade,
                'gradeStatus' => $grade->gradeStatus,
                'academic_year' => $grade->academic_year,
                'current_semester' => $grade->current_semester ?? null,
            ];
        });

        return response()->json([
            'grades' => $gradesWithSubject,
            'gpa' => $gpa,
            'cgpa' => $cgpa
        ], 200);
    }
}

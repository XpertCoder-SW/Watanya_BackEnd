<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="Watanya APIs",
 *     version="1.0.0",
 * )
 *
 * @OA\Tag(
 *     name="Setting",
 * )
 */
class AdminsController
{
    /**
     * @OA\Post(
     *     path="/api/setting",
     *     summary="Create new system setting",
     *     description="Create new system setting with showGrades, academic_year, and current_semester.",
     *     tags={"Setting"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"showGrades", "academic_year", "current_semester"},
     *             @OA\Property(property="showGrades", type="boolean", example=true),
     *             @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *             @OA\Property(property="current_semester", type="string", example="One")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="System setting created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="showGrades", type="boolean", example=true),
     *                 @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *                 @OA\Property(property="current_semester", type="string", example="One")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request) //POST API
    {
        $validator = Validator::make($request->all(), [
            'showGrades' => 'required|boolean',
            'academic_year' => 'required|string',
            'current_semester' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = Admin::create($request->only(['showGrades', 'academic_year', 'current_semester']));

        return response()->json([
            'data' => [
                'id' => $admin->id,
                'showGrades' => $admin->showGrades,
                'academic_year' => $admin->academic_year,
                'current_semester' => $admin->current_semester
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/setting",
     *     summary="Get system setting",
     *     description="Retrieve all system setting including id, showGrades, academic_year, and current_semester.",
     *     tags={"Setting"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="showGrades", type="boolean", example=true),
     *             @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *             @OA\Property(property="current_semester", type="string", example="One")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No system setting found"
     *     )
     * )
     */


    public function index()
    {
        $admin = Admin::first();

        if (!$admin) {
            return response()->json(['message' => 'No system setting found'], 404);
        }

        return response()->json([
            'id' => $admin->id,
            'showGrades' => $admin->showGrades,
            'academic_year' => $admin->academic_year,
            'current_semester' => $admin->current_semester
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/setting/{id}",
     *     summary="Update system setting",
     *     description="Update existing system setting by ID.",
     *     tags={"Setting"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the setting to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"showGrades", "academic_year", "current_semester"},
     *             @OA\Property(property="showGrades", type="boolean", example=true),
     *             @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *             @OA\Property(property="current_semester", type="string", example="One")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="System setting updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="System setting updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="showGrades", type="boolean", example=true),
     *                 @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *                 @OA\Property(property="current_semester", type="string", example="One")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="System settings not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'showGrades' => 'required|boolean',
            'academic_year' => 'required|string',
            'current_semester' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['message' => 'System setting not found'], 404);
        }

        $oldAcademicYear = $admin->academic_year;
        $admin->update($request->only(['showGrades', 'academic_year', 'current_semester']));

        // Update all students' academic_year if it was changed
        if ($request->academic_year !== $oldAcademicYear) {
            Student::query()->update(['academic_year' => $request->academic_year]);
        }

        return response()->json([
            'data' => [
                'id' => $admin->id,
                'showGrades' => $admin->showGrades,
                'academic_year' => $admin->academic_year,
                'current_semester' => $admin->current_semester
            ]
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/setting/{id}",
     *     summary="Delete system setting",
     *     description="Delete a system setting by its ID.",
     *     tags={"Setting"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the setting to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="System setting deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin setting deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="System setting not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin setting not found")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['message' => 'Admin setting not found'], 404);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin setting deleted successfully'], 200);
    }

}

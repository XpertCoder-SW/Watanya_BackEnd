<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Admin Doctors",
 * )
 */
class DoctorsController
{
/**
 * @OA\Get(
 *     path="/api/admin/doctors",
 *     tags={"Admin Doctors"},
 *     summary="Get paginated list of doctors",
 *     description="Returns a paginated list of doctors with their subjects",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search term for doctor name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="code",
 *         in="query",
 *         description="Search term for doctor code",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="per_page", type="integer", example=10),
 *             @OA\Property(property="total_pages", type="integer", example=5),
 *             @OA\Property(
 *                 property="doctors",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed"),
 *                     @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *                     @OA\Property(property="phoneNumber", type="string", example="+201234567890"),
 *                     @OA\Property(property="code", type="string", example="DOC123"),
 *                     @OA\Property(
 *                         property="subjects",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="name", type="string", example="Anatomy")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $code = $request->input('code'); // <-- Add this line

    $query = Doctor::with(['subjects:id,name']);

    if ($search) {
        $query->where('name', 'like', '%' . $search . '%');
    }

    if ($code) { // <-- Add this block
        $query->where('code', 'like', '%' . $code . '%');
    }

    $paginatedDoctors = $query->select(['id', 'name', 'email', 'phoneNumber', 'code'])
        ->paginate($perPage, ['*'], 'page', $page);

    // إخفاء حقل pivot من كل موضوع في كل طبيب
    $paginatedDoctors->getCollection()->transform(function($doctor) {
        $doctor->subjects->each->makeHidden('pivot');
        return $doctor;
    });

    return response()->json([
        'current_page' => $paginatedDoctors->currentPage(),
        'per_page' => $paginatedDoctors->perPage(),
        'total_pages' => $paginatedDoctors->lastPage(),
        'doctors' => $paginatedDoctors->items()
    ], 200);
}

    /**
     * @OA\Post(
     *     path="/api/admin/doctors",
     *     tags={"Admin Doctors"},
     *     summary="Create a new doctor",
     *     description="Create a new doctor with the specified data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phoneNumber","subject_ids","code","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed"),
     *             @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
     *             @OA\Property(property="phoneNumber", type="string", example="+201234567890"),
     *             @OA\Property(
     *                 property="subject_ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of subject IDs"
     *             ),
     *             @OA\Property(property="code", type="string", example="DOC123"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="doctor",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed"),
     *                 @OA\Property(property="email", type="string", example="ahmed@example.com"),
     *                 @OA\Property(property="phoneNumber", type="string", example="+201234567890"),
     *                 @OA\Property(property="code", type="string", example="DOC123"),
     *                 @OA\Property(
     *                     property="subjects",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Anatomy")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:doctors,email',
        'phoneNumber' => 'required|string|max:255',
        'subject_ids' => 'required|array',
        'subject_ids.*' => 'exists:subjects,id',
        'code' => 'required|string|max:255|unique:doctors,code',
        'password' => 'required|string|confirmed',
    ]);

    $doctor = Doctor::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phoneNumber' => $validated['phoneNumber'],
        'code' => $validated['code'],
        'password' => Hash::make($validated['password']),
    ]);

    $doctor->subjects()->attach($validated['subject_ids']);

    // تحميل المواد مع تحديد الجدول بوضوح
    $doctor->load(['subjects' => function($query) {
        $query->select('subjects.id', 'subjects.name');
    }]);

    // إخفاء حقل pivot يدوياً
    $doctor->subjects->each->setHidden(['pivot']);

    return response()->json([
        'doctor' => [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phoneNumber' => $doctor->phoneNumber,
            'code' => $doctor->code,
            'subjects' => $doctor->subjects->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name
                ];
            })
        ]
    ], 200);
}

/**
 * @OA\Put(
 *     path="/api/admin/doctors/{id}",
 *     tags={"Admin Doctors"},
 *     summary="Update a doctor",
 *     description="Update an existing doctor's data. Password is optional - if not provided, the existing password will be kept.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the doctor to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed Updated"),
 *             @OA\Property(property="email", type="string", format="email", example="ahmed.updated@example.com"),
 *             @OA\Property(property="phoneNumber", type="string", example="+201234567891"),
 *             @OA\Property(
 *                 property="subject_ids",
 *                 type="array",
 *                 @OA\Items(type="integer", example=2),
 *                 description="Array of subject IDs"
 *             ),
 *             @OA\Property(property="code", type="string", example="DOC124"),
 *             @OA\Property(
 *                 property="password",
 *                 type="string",
 *                 format="password",
 *                 example="newpassword123",
 *                 description="Optional - if not provided, existing password will be kept"
 *             ),
 *             @OA\Property(
 *                 property="password_confirmation",
 *                 type="string",
 *                 format="password",
 *                 example="newpassword123",
 *                 description="Required only if password is provided"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Doctor updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="doctor",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed Updated"),
 *                 @OA\Property(property="email", type="string", example="ahmed.updated@example.com"),
 *                 @OA\Property(property="phoneNumber", type="string", example="+201234567891"),
 *                 @OA\Property(property="code", type="string", example="DOC124"),
 *                 @OA\Property(
 *                     property="subjects",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=2),
 *                         @OA\Property(property="name", type="string", example="Physiology")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Doctor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Doctor not found")
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
 *                     property="password",
 *                     type="array",
 *                     @OA\Items(type="string", example="The password confirmation does not match.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function update(Request $request, $id)
{
    // Find the doctor
    $doctor = Doctor::find($id);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    // Validation rules
    $rules = [
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:doctors,email,' . $doctor->id,
        'phoneNumber' => 'sometimes|string|unique:doctors,phoneNumber,' . $doctor->id,
        'code' => 'sometimes|string|unique:doctors,code,' . $doctor->id,
        'subject_ids' => 'sometimes|array',
        'subject_ids.*' => 'exists:subjects,id',
        'password' => 'sometimes|string|confirmed',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Update basic doctor info (excluding password)
    $doctor->fill($request->only(['name', 'email', 'phoneNumber', 'code']));

    // Update password only if provided
    if ($request->filled('password')) {
        $doctor->password = Hash::make($request->password);
    }

    $doctor->save();

    // Update subjects if provided
    if ($request->has('subject_ids')) {
        $doctor->subjects()->sync($request->subject_ids);
    }

    // Load the updated doctor with subjects
    $doctor->load(['subjects' => function($query) {
        $query->select('subjects.id', 'subjects.name');
    }]);

    // Hide timestamps from doctor model
    $doctor->makeHidden(['created_at', 'updated_at', 'password']);
    $doctor->subjects->each->makeHidden(['pivot', 'created_at', 'updated_at']);

    return response()->json([
        'doctor' => $doctor
    ], 200);
}

    /**
     * @OA\Delete(
     *     path="/api/admin/doctors/{id}",
     *     tags={"Admin Doctors"},
     *     summary="Delete a doctor",
     *     description="Delete a doctor by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the doctor to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doctor deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found"
     *     )
     * )
     */
    public function delete($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->subjects()->detach();
        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/doctors/{id}",
     *     tags={"Admin Doctors"},
     *     summary="Get doctor by ID",
     *     description="Retrieve a single doctor by their ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the doctor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Dr. Ahmed Mohamed"),
     *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
     *             @OA\Property(property="phoneNumber", type="string", example="+201234567890"),
     *             @OA\Property(property="code", type="string", example="DOC123"),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Anatomy")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doctor not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $doctor = Doctor::with(['subjects:id,name'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        // Hide pivot field from subjects
        $doctor->subjects->each->makeHidden('pivot');

        return response()->json($doctor);
    }
}

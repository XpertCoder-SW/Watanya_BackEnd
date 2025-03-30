<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorsController
{
    // Get All Doctors to be shown in the first page of Doctors
    public function index()
    {
        $doctors = Doctor::all(['name', 'email', 'phoneNumber']);

        return response()->json(['doctors' => $doctors], 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'phoneNumber' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
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

        // Linking the doctor to the subject
        $doctor->subjects()->attach($validated['subject_id']);

        return response()->json(['message' => 'Doctor added successfully', 'doctor' => $doctor], 201);
    }


    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:doctors,email,{$id}",
            'phone_number' => 'sometimes|string|max:20',
            'subject_id' => 'sometimes|exists:subjects,id',
            'login_code' => "sometimes|string|max:50|unique:doctors,login_code,{$id}",
            'password' => 'sometimes|string|min:8|confirmed',
        ]);


        
        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }

        if (isset($validated['phone_number'])) {
            $updateData['phone_number'] = $validated['phone_number'];
        }

        if (isset($validated['login_code'])) {
            $updateData['login_code'] = $validated['login_code'];
        }

        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Ensure that the user has submitted at least one field for modification.
        if (empty($updateData) && !isset($validated['subject_id'])) {
            return response()->json([
                'message' => 'At least one field must be provided to update the doctor.'
            ], 422);
        }

        // Edit doctor data in database
        $updated = Doctor::where('id', "$id")->update($updateData);

        // Verify that the doctor is available
        if (!$updated) {
            return response()->json([
                'message' => 'Doctor not found.'
            ], 404);
        }

        // Bring Dr. after modification 
        $doctor = Doctor::find($id);

        // Edit the associated Subject (if subject_id was sent)
        if (isset($validated['subject_id'])) {
            $doctor->subjects()->attach($validated['subject_id']);
        }

        // get Subjects with Dr 
        $doctor->load('subjects');

        // return response
        return response()->json([
            'message' => 'Doctor updated successfully',
            'doctor' => $doctor
        ], 201);
    }
}

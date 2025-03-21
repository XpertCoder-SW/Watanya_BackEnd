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


    public function update(Request $request , $id){

    }
}

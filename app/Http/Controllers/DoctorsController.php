<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorsController
{
    // Get All Doctors to be shown in the first page of Doctors
    public function index()
    {
        $doctors = Doctor::all(['name', 'email', 'phoneNumber']);

        return response()->json(['doctors' => $doctors], 200);
    }
}

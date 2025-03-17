<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminsController
{
    public function index()
    {
        $admin = Admin::all(['showGrades', 'academic_year', 'current_semester'])->first();
        return response()->json($admin, 200);
    }

    public function showGrade()
    {
        $admin = Admin::all(['showGrades'])->first();
        return response()->json($admin, 200);
    }

    public function update(Request $request){

    }
}

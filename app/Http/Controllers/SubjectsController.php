<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectsController
{
    // Get All Subject to be shown in the first page of subjects
    public function index(){
        $subjects = Subject::all(['code', 'name', 'creditHours', 'specialization', 'level', 'semester']);
        return response()->json(['subjects' => $subjects], 200);
    }


}

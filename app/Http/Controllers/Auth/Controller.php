<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Student;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        $code = $request->input('code');
        $password = $request->input('password');

        $user = null;
        $table = null;

        $user = Admin::where('code', $code)->first();
        if ($user) {
            $table = 'admins';
        }

        if (!$user) {
            $user = Student::where('code', $code)->first();
            if ($user) {
                $table = 'students';
            }
        }

        if (!$user) {
            $user = Doctor::where('code', $code)->first();
            if ($user) {
                $table = 'doctors';
            }
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $payload = [
            'id' => $user->id,
            'code' => $user->code,
            'table' => $table,
            'iat' => time(),
            'exp' => time() + 3600 //For 1H
        ];

        $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return response()->json([
            'message' => 'Login successful',
            'token' => $jwt,
            'user' => $user
        ]);
    }
}

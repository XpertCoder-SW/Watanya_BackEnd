<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Student;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Post(
 *     path="/api/login",
 *     summary="Login user",
 *     description="Authenticate user and return JWT token",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"code", "password"},
 *             @OA\Property(property="code", type="string", example="admin"),
 *             @OA\Property(property="password", type="string", example="admin")
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Login successful"),
 *             @OA\Property(property="token", type="string"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */

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

        // Doctor login
        if (!$user) {
            $user = Doctor::where('code', $code)->first();
            if ($user && Hash::check($password, $user->password)) {
                $table = 'doctors';
            }
        }

        // Student login using code as password
        if (!$user) {
            $user = Student::where('code', $code)->first();
            if ($user && $code === $password) {
                $table = 'students';
            }
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $role = null;
        if ($table === 'admins') {
            $role = 'Admin';
        } elseif ($table === 'doctors') {
            $role = 'Doctor';
        } elseif ($table === 'students') {
            $role = 'Student';
        }

        $payload = [
            'id' => $user->id,
            'code' => $user->code,
            'name' => $user->name,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + 3600 // 1 hour expiration for access token
        ];

        $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return response()->json([
            'message' => 'Login successful',
            'token' => $jwt,
            'refresh_token' => $jwt, // Use the same JWT as the refresh token
            'role' => $role,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentDetails;
use App\Models\ResponderDetails;
use \App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * LOGIN
     * Optimization: Uses the 'login_id' Index for O(1) lookup speed.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'id'       => 'required', 
            'password' => 'required|string',
        ]);

        $user = User::where('login_id', $validated['id'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Incorrect ID or password.'],
            ]);
        }

        // --- REQUIREMENT MET: Login History ---
        // We record the login action immediately after password check passes.
        ActivityLog::create([
            'user_id'    => $user->id,
            'action'     => 'LOGIN',
            'details'    => 'User logged in via API',
        ]);

        $token = $user->createToken($user->role . '-token')->plainTextToken;

        $profile = match($user->role) {
            'student'   => $user->studentDetails,
            'responder' => $user->responderDetails,
            default     => null,
        };

        // --- OPTIMIZATION: Fix Redundant Data ---
        // Detach the relationship from the main user object so it's not sent twice.
        $user->unsetRelation('studentDetails');
        $user->unsetRelation('responderDetails');

        return response()->json([
            'role'    => $user->role,
            'user'    => $user,    // Clean User object
            'details' => $profile, // Specific Details object
            'token'   => $token,
        ], 200);
    }

    /**
     * REGISTER
     * Logic: Handles De-normalization (Phone/Name) and Vertical Partitioning (Details)
     */
    public function register(Request $request)
    {
        // 1. Validate Common Data
        $validated = $request->validate([
            'type'         => 'required|in:student,responder,admin',
            'name'         => 'required|string|max:255',
            'password'     => 'required|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20', // Common field
            
            // Conditional Validation
            'student_id'   => 'required_if:type,student|unique:users,login_id',
            'email'        => 'required_if:type,responder,admin|email|unique:users,login_id',
            
            // Detail Validation
            'department'   => 'nullable|required_if:type,student|string',
            'year_level'   => 'nullable|required_if:type,student|integer|between:1,4',
            'position'     => 'nullable|required_if:type,responder|string',
        ]);

        // 2. Determine Login ID (Student ID vs Email)
        $loginId = $validated['type'] === 'student' ? $validated['student_id'] : $validated['email'];

        // 3. Create Main User (De-normalized data goes here)
        $user = User::create([
            'login_id'     => $loginId,
            'password'     => Hash::make($validated['password']),
            'name'         => $validated['name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'role'         => $validated['type'],
        ]);

        // 4. Create Vertical Partition (Specific Details)
        $details = null;
        
        if ($validated['type'] === 'student') {
            $details = StudentDetails::create([
                'user_id'    => $user->id,
                'department' => $validated['department'],
                'year_level' => $validated['year_level'],
            ]);
        } elseif ($validated['type'] === 'responder') {
            $details = ResponderDetails::create([
                'user_id'  => $user->id,
                'position' => $validated['position'],
            ]);
        }

        $token = $user->createToken($user->role . '-token')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user,
            'details' => $details,
            'token'   => $token
        ], 201);
    }
}
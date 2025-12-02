<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * SHOW: Get the currently logged-in user's full profile
     * Optimization: Uses Eager Loading to get data from both tables in 1 query.
     */
    public function show(Request $request)
    {
        // 1. Get current User
        $user = $request->user();

        // 2. Load the specific partition data based on role
        // This avoids loading 'responderDetails' for a student (which would be null anyway)
        if ($user->role === 'student') {
            $user->load('studentDetails');
        } elseif ($user->role === 'responder') {
            $user->load('responderDetails');
        }

        return response()->json($user);
    }

    /**
     * UPDATE: Update self (Common info + Role-specific info)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validation - Common Fields
        $rules = [
            'name'         => 'sometimes|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password'     => 'nullable|min:8|confirmed',
        ];

        // 2. Validation - Dynamic Role Fields
        if ($user->role === 'student') {
            $rules['department'] = 'nullable|string';
            $rules['year_level'] = 'nullable|integer|between:1,4';
        } elseif ($user->role === 'responder') {
            $rules['position'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // 3. Update Main User Table
        $user->fill($request->only(['name', 'phone_number']));
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        // 4. Update Partition Table (Role Specific)
        if ($user->role === 'student') {
            // updateOrCreate ensures it works even if the details row was somehow missing
            $user->studentDetails()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only(['department', 'year_level'])
            );
        } elseif ($user->role === 'responder') {
            $user->responderDetails()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only(['position'])
            );
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh(['studentDetails', 'responderDetails']) // Reload data to show changes
        ]);
    }
}
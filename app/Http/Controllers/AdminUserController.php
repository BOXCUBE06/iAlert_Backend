<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentDetails;
use App\Models\ResponderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * READ: Get list of users (Filtered by Role)
     * Optimization: Uses Eager Loading 'with()' to fetch profile details in 1 query.
     */
    public function index(Request $request)
    {
        // Allow filtering by role (e.g., ?role=student)
        $role = $request->query('role');

        $query = User::query();

        if ($role) {
            $query->where('role', $role); // Uses the 'role' INDEX
        } else {
            // If no filter, don't show other admins, just students/responders
            $query->whereIn('role', ['student', 'responder']);
        }

        // Optimization: Eager Load relationships to avoid N+1 query performance issues
        $users = $query->with(['studentDetails', 'responderDetails'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(15); // Use Pagination for large datasets

        return response()->json($users);
    }

    /**
     * CREATE: Add a new Student or Responder
     * Logic: Identical to Register, but wrapped in DB Transaction for safety.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|in:student,responder', // Admin can't create other Admins via this API usually
            'name'         => 'required|string|max:255',
            'password'     => 'required|min:8', // No confirmation needed for Admin entry usually
            'phone_number' => 'nullable|string|max:20',
            
            // Conditional Validation
            'student_id'   => 'required_if:type,student|unique:users,login_id',
            'email'        => 'required_if:type,responder|email|unique:users,login_id',
            
            // Details
            'department'   => 'nullable|required_if:type,student|string',
            'year_level'   => 'nullable|required_if:type,student|integer|between:1,4',
            'position'     => 'nullable|required_if:type,responder|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $loginId = $validated['type'] === 'student' ? $validated['student_id'] : $validated['email'];

            // 1. Create User
            $user = User::create([
                'login_id'     => $loginId,
                'password'     => Hash::make($validated['password']),
                'name'         => $validated['name'],
                'phone_number' => $validated['phone_number'] ?? null,
                'role'         => $validated['type'],
            ]);

            // 2. Create Details based on type
            if ($validated['type'] === 'student') {
                StudentDetails::create([
                    'user_id'    => $user->id,
                    'department' => $validated['department'],
                    'year_level' => $validated['year_level'],
                ]);
            } elseif ($validated['type'] === 'responder') {
                ResponderDetails::create([
                    'user_id'  => $user->id,
                    'position' => $validated['position'],
                ]);
            }

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        });
    }

    /**
     * SHOW: Get single user details
     */
    public function show($id)
    {
        $user = User::with(['studentDetails', 'responderDetails'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * UPDATE: Edit User and their Profile
     * Logic: Updates main table, then checks role to update the correct partition table.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password'     => 'nullable|min:8', 
            
            // Unique check must ignore current user's ID
            'student_id'   => ['nullable', 'required_if:type,student', Rule::unique('users', 'login_id')->ignore($user->id)],
            'email'        => ['nullable', 'required_if:type,responder', 'email', Rule::unique('users', 'login_id')->ignore($user->id)],

            // Details
            'department'   => 'nullable|string',
            'year_level'   => 'nullable|integer|between:1,4',
            'position'     => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $user, $validated) {
            
            // 1. Update Main User Data
            $userData = [];
            if ($request->has('name')) $userData['name'] = $validated['name'];
            if ($request->has('phone_number')) $userData['phone_number'] = $validated['phone_number'];
            if ($request->has('password')) $userData['password'] = Hash::make($validated['password']);
            
            // Handle Login ID change if provided
            if ($user->role === 'student' && $request->has('student_id')) {
                $userData['login_id'] = $validated['student_id'];
            } elseif ($user->role === 'responder' && $request->has('email')) {
                $userData['login_id'] = $validated['email'];
            }
            
            $user->update($userData);

            // 2. Update Partition Data
            if ($user->role === 'student') {
                $user->studentDetails()->update([
                    'department' => $request->input('department', $user->studentDetails->department),
                    'year_level' => $request->input('year_level', $user->studentDetails->year_level),
                ]);
            } elseif ($user->role === 'responder') {
                $user->responderDetails()->update([
                    'position' => $request->input('position', $user->responderDetails->position),
                ]);
            }

            return response()->json(['message' => 'User updated successfully', 'user' => $user->fresh(['studentDetails', 'responderDetails'])]);
        });
    }

    /**
     * DELETE: Remove user
     * Optimization: Cascade Delete in Migration handles the profile cleanup automatically.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // 2. USE Auth::id() HERE
        // This checks if the ID of the user you are deleting matches the currently logged-in admin
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 403);
        }

        $user->delete(); 

        return response()->json(['message' => 'User deleted successfully']);
    }
}
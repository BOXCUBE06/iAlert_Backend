<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Import the NEW Optimized Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;      // Replaces Student/ResponderController for profile updates
use App\Http\Controllers\AdminUserController;    // Replaces Student/ResponderController for Admin CRUD
use App\Http\Controllers\AlertController;        // Handles Alerts + Merged Responses
use App\Http\Controllers\NotificationController; // Only Read/Mark Read
use App\Http\Controllers\ActivityLogController;  // New Audit Logs

/*
|--------------------------------------------------------------------------
| API Routes (Optimized for iAlert)
|--------------------------------------------------------------------------
*/

// --------------------
// 1. PUBLIC ROUTES
// --------------------
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // Used by students/responders to sign up

// --------------------
// 2. PROTECTED ROUTES (Requires Login)
// --------------------
Route::middleware('auth:sanctum')->group(function () {

    // --- COMMON ROUTES (Everyone can access) ---
    
    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    // Profile Management (Unified Controller)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);


    // --- ROLE SPECIFIC ROUTES ---

    // A. STUDENT ROUTES
    Route::middleware('role:student')->group(function () {
        // Create an emergency alert
        Route::post('/alerts', [AlertController::class, 'store']);
        
        // Update live location (Hot/Cold data split)
        Route::put('/alerts/{id}/location', [AlertController::class, 'updateLocation']);
    });

    // B. RESPONDER ROUTES
    Route::middleware('role:responder')->group(function () {
        // View Active Map
        Route::get('/responder/alerts', [AlertController::class, 'index']);
        
        // Actions (Merged Logic)
        Route::post('/alerts/{id}/accept', [AlertController::class, 'accept']);
        Route::post('/alerts/{id}/arrived', [AlertController::class, 'arrived']);
        Route::post('/alerts/{id}/resolve', [AlertController::class, 'resolve']);
    });

    // C. ADMIN ROUTES
    Route::middleware('role:admin')->group(function () {
        // User Management (Unified for Students & Responders)
        // This one line creates index, show, store, update, destroy routes
        Route::apiResource('/admin/users', AdminUserController::class);

        // View System Logs (Audit Trail)
        Route::get('/admin/logs', [ActivityLogController::class, 'index']);
        
        // Admin View of Alerts (Filterable history)
        Route::get('/admin/alerts', [AlertController::class, 'index']);
    });

});
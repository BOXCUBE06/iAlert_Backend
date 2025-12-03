<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;      
use App\Http\Controllers\AdminUserController;    
use App\Http\Controllers\AlertController;
use App\Http\Controllers\NotificationController; 
use App\Http\Controllers\ActivityLogController;



// 1. PUBLIC ROUTES
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // Used by students/responders to sign up

// 2. PROTECTED ROUTES (Requires Login)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // STUDENT ROUTES
    Route::middleware('role:student')->group(function () {
        // Create an emergency alert
        Route::post('/alerts', [AlertController::class, 'store']);
        
        // Update location
        Route::put('/alerts/{id}/location', [AlertController::class, 'updateLocation']);
    });

    // RESPONDER ROUTES
    Route::middleware('role:responder')->group(function () {
        // View Active Map
        Route::get('/responder/alerts', [AlertController::class, 'index']);
        
        // Actions
        Route::post('/alerts/{id}/accept', [AlertController::class, 'accept']);
        Route::post('/alerts/{id}/arrived', [AlertController::class, 'arrived']);
        Route::post('/alerts/{id}/resolve', [AlertController::class, 'resolve']);
    });

    // ADMIN ROUTES
    Route::middleware('role:admin')->group(function () {
        // all crud operations
        Route::apiResource('/admin/users', AdminUserController::class);

        // View System Logs
        Route::get('/admin/logs', [ActivityLogController::class, 'index']);
        
        // Admin View of Alerts
        Route::get('/admin/alerts', [AlertController::class, 'index']);
    });

});
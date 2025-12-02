<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the current user
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user(); 

        $notifications = $user->notifications()
                              ->whereNull('read_at')
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json($notifications);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(Request $request, $id) // Added Request here
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = $user->notifications()->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * Mark ALL as read
     */
    public function markAllAsRead(Request $request) // Added Request here
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->notifications()
             ->whereNull('read_at')
             ->update(['read_at' => now()]);

        return response()->json(['message' => 'All marked as read']);
    }
}
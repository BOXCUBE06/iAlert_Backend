<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); 

        $notifications = $user->notifications()
                              ->whereNull('read_at')
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json($notifications);
    }

    
     //Mark a single notification as read
     
    public function markAsRead(Request $request, $id) // Added Request here
    {
        $user = $request->user();

        $notification = $user->notifications()->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    
    //Mark ALL as read
     
    public function markAllAsRead(Request $request) // Added Request here
    {
    
        $user = $request->user();

        $user->notifications()
             ->whereNull('read_at')
             ->update(['read_at' => now()]);

        return response()->json(['message' => 'All marked as read']);
    }
}
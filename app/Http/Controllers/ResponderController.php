<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponderController extends Controller
{
    /**
     * HEARTBEAT: Responder sends this every 10 seconds
     */
    public function updateHeartbeat(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_online' => 'required|boolean'
        ]);
           
        $user = User::findOrFail(Auth::id());

        // Update tracking info
        
        $user->responderDetails()->update([
            'current_latitude'  => $request->latitude,
            'current_longitude' => $request->longitude,
            'is_online'         => $request->is_online,
            'last_seen_at'      => now(), // Stamp the time
        ]);

        return response()->json(['message' => 'Heartbeat acknowledged']);
    }

    /**
     * MAP DATA: Get responders who are online & active
     */
    public function getActiveResponders()
    {
        // "Active" means: marked online AND sent a heartbeat in the last 2 minutes
        $responders = User::where('role', 'responder')
            ->whereHas('responderDetails', function ($q) {
                $q->where('is_online', true)
                  ->whereNotNull('current_latitude')
                  ->where('last_seen_at', '>=', now()->subMinutes(2)); 
            })
            ->with('responderDetails')
            ->get();

        // Format for Flutter
        $data = $responders->map(function ($r) {
            return [
                'id'       => $r->id,
                'name'     => $r->name,
                'position' => $r->responderDetails->position, 
                'lat'      => (float) $r->responderDetails->current_latitude,
                'lng'      => (float) $r->responderDetails->current_longitude,
            ];
        });

        return response()->json($data);
    }
}
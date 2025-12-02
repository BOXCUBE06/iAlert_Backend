<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\LocationHistory;
use App\Models\Notification; // Ensure this is imported
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * STUDENT: Create a new Emergency Alert
     */
    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'category'    => 'required|string',
            'severity'    => 'required|in:mild,moderate,severe',
            'description' => 'nullable|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
        ]);

        $user = $request->user();

        // 2. Prevent Spam
        // $existing = Alert::where('student_id', $user->id)
        //                  ->whereIn('status', ['pending', 'accepted', 'arrived'])
        //                  ->first();

        // if ($existing) {
        //     return response()->json(['message' => 'You already have an active alert.', 'alert' => $existing], 409);
        // }

        // 3. Create Alert
        $alert = Alert::create([
            'student_id'    => $user->id,
            'student_name'  => $user->name,
            'student_phone' => $user->phone_number,
            'category'      => $request->category,
            'severity'      => $request->severity,
            'description'   => $request->description,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'status'        => 'pending',
        ]);

        // 4. Log Initial Location
        LocationHistory::create([
            'alert_id'  => $alert->id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // 5. NOTIFY ADMINS & RESPONDERS
        $recipients = User::whereIn('role', ['admin', 'responder'])->get();

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id'  => $recipient->id,
                'alert_id' => $alert->id,
                'title'    => 'EMERGENCY ALERT',
                'message'  => "{$user->name} needs help! Severity: {$request->severity}",
                'type'     => 'alert',
            ]);
        }

        return response()->json($alert, 201);
    }

    /**
     * STUDENT: Update Live Location
     */
    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $alert = Alert::findOrFail($id);

        if ($alert->student_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($alert->status === 'resolved' || $alert->status === 'cancelled') {
            return response()->json(['message' => 'Alert is closed'], 400);
        }

        DB::transaction(function () use ($alert, $request) {
            // Update Map
            $alert->update([
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Update History
            LocationHistory::create([
                'alert_id'  => $alert->id,
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        });

        return response()->json(['message' => 'Location updated']);
    }

    /**
     * ADMIN/RESPONDER: Get Active Alerts
     */
    public function index(Request $request)
    {
        $query = Alert::query();

        // 1. FILTER BY STATUS
        // Default: Show active alerts (pending, accepted, arrived)
        // If frontend sends ?status=resolved, show history.
        // If frontend sends ?status=all, show everything.
        if ($request->has('status')) {
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            // Default behavior (Map View)
            $query->whereIn('status', ['pending', 'accepted', 'arrived']);
        }

        // 2. FILTER BY SEVERITY (Optional)
        // Example: ?severity=critical
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        // 3. FILTER BY DATE (Optional - for Reports)
        // Example: ?date=2025-11-25
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 4. SORTING
        // Default to newest first
        $query->orderBy('created_at', 'desc');

        // 5. PAGINATION vs LIST
        // If we are asking for "Resolved" (History), we should paginate (page 1, 2, 3).
        // If we are asking for "Active" (Map), we need ALL of them (no pagination).
        if ($request->status === 'resolved' || $request->status === 'cancelled') {
            return response()->json($query->paginate(20));
        }

        return response()->json($query->get());
    }

    /**
     * RESPONDER: Accept an Alert
     * FIXED: Now notifies the Student correctly.
     */
    public function accept(Request $request, $id)
    {
        $alert = Alert::findOrFail($id);

        if ($alert->status !== 'pending') {
            return response()->json(['message' => 'Alert is not pending'], 409);
        }

        $responder = Auth::user(); // Get the responder who is accepting

        $alert->update([
            'status'       => 'accepted',
            'responder_id' => $responder->id,
            'responded_at' => now(),
        ]);

        // --- FIX STARTS HERE ---
        // Notify the STUDENT that help is coming
        Notification::create([
            'user_id'  => $alert->student_id, // Send to the Student
            'alert_id' => $alert->id,
            'title'    => 'Help is on the way',
            'message'  => "Responder {$responder->name} has accepted your alert.",
            'type'     => 'success',
        ]);
        // --- FIX ENDS HERE ---

        return response()->json(['message' => 'Alert accepted', 'alert' => $alert]);
    }

    /**
     * RESPONDER: Mark as Arrived
     */
    public function arrived($id)
    {
        $alert = Alert::findOrFail($id);

        if ($alert->responder_id !== Auth::id()) {
            return response()->json(['message' => 'You are not the assigned responder'], 403);
        }

        $alert->update([
            'status'     => 'arrived',
            'arrived_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated to Arrived', 'alert' => $alert]);
    }

    /**
     * RESPONDER/ADMIN: Resolve (Close) the Alert
     */
    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);

        $user = Auth::user();
        if ($user->role !== 'admin' && $alert->responder_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Alert resolved', 'alert' => $alert]);
    }
}
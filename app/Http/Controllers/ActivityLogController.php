<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
       public function index(Request $request)
    {
        // Start the query
        // OPTIMIZATION: Eager load 'user' to avoid N+1 query problem when listing names
        $query = ActivityLog::with('user:id,name,role,login_id'); 

        // Optional Filter: Search by specific User ID
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Optional Filter: Search by Action (e.g., "ALERT_CREATED")
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Return latest logs first, paginated
        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate(20);

        return response()->json($logs);
    }
}
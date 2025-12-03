<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
       public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name,role,login_id'); 

        //Filter: Search by specific User ID
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        //Filter: Search by Action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Return latest logs first, paginated
        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate(20);

        return response()->json($logs);
    }
}
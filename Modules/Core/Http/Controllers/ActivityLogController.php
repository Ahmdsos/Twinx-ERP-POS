<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Core\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ActivityLogController
 * Audit log viewer for administrators
 */
class ActivityLogController extends Controller
{
    /**
     * Display activity log listing
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_name', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate(50);

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = ActivityLog::distinct()->pluck('action');

        return view('admin.activity-log.index', compact('activities', 'users', 'actions'));
    }

    /**
     * Show details of a single activity
     */
    public function show(ActivityLog $activityLog)
    {
        return view('admin.activity-log.show', compact('activityLog'));
    }

    /**
     * Get activity for a specific subject (AJAX)
     */
    public function forSubject(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $activities = ActivityLog::forSubject($request->type, $request->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($activities);
    }
}

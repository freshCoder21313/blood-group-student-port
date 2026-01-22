<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.activity-logs.index', compact('logs'));
    }
}

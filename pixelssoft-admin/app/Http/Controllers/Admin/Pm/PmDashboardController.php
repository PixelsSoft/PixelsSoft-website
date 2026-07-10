<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Project;
use App\Models\Pm\Task;
use App\Models\Pm\TimeEntry;

class PmDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        return view('admin.pm.dashboard', [
            'stats' => [
                'projects' => Project::count(),
                'active_projects' => Project::where('status', 'active')->count(),
                'open_tasks' => Task::whereNot('status', 'done')->count(),
                'my_tasks' => Task::where('assignee_id', $userId)->whereNot('status', 'done')->count(),
                'pending_time' => TimeEntry::whereNull('approved_at')->count(),
            ],
            'recentProjects' => Project::with('manager')->latest()->take(5)->get(),
            'myTasks' => Task::with('project')->where('assignee_id', $userId)->whereNot('status', 'done')->latest()->take(5)->get(),
        ]);
    }
}

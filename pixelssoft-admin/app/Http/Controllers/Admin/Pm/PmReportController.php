<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Project;
use App\Models\Pm\Task;
use App\Models\Pm\TimeEntry;
use Illuminate\Support\Facades\DB;

class PmReportController extends Controller
{
    public function index()
    {
        return view('admin.pm.reports.index', [
            'hoursByProject' => TimeEntry::select('project_id', DB::raw('sum(hours) as hours'))
                ->groupBy('project_id')
                ->with('project')
                ->orderByDesc('hours')
                ->get(),
            'hoursByUser' => TimeEntry::select('user_id', DB::raw('sum(hours) as hours'))
                ->groupBy('user_id')
                ->with('user')
                ->orderByDesc('hours')
                ->get(),
            'billableHours' => TimeEntry::where('billable', true)->sum('hours'),
            'nonBillableHours' => TimeEntry::where('billable', false)->sum('hours'),
            'projectsAtRisk' => Project::where('status', 'active')
                ->get()
                ->filter(fn ($p) => $p->budget_hours > 0 && $p->budgetBurnPercent() >= 80),
            'tasksByStatus' => Task::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Activity;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;

class CrmDashboardController extends Controller
{
    public function index()
    {
        $pipeline = Pipeline::where('is_default', true)->with('stages')->first();

        return view('admin.crm.dashboard', [
            'stats' => [
                'leads' => Lead::count(),
                'new_leads' => Lead::where('status', 'new')->count(),
                'deals' => Deal::count(),
                'open_deals' => Deal::whereNull('won_at')->whereNull('lost_reason')->count(),
                'pipeline_value' => Deal::whereNull('won_at')->whereNull('lost_reason')->sum('value'),
                'won_deals' => Deal::whereNotNull('won_at')->count(),
            ],
            'recentLeads' => Lead::with('owner')->latest()->take(5)->get(),
            'pipeline' => $pipeline,
            'overdueActivities' => Activity::whereNull('completed_at')
                ->where('due_at', '<', now())
                ->with('user')
                ->latest('due_at')
                ->take(5)
                ->get(),
        ]);
    }
}

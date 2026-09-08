<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Activity;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use App\Support\ScopesByOwner;

class CrmDashboardController extends Controller
{
    use ScopesByOwner;

    public function index()
    {
        $pipeline = Pipeline::where('is_default', true)->with('stages')->first();
        $leads = $this->scopeForCurrentUser(Lead::query());
        $deals = $this->scopeForCurrentUser(Deal::query());

        return view('admin.crm.dashboard', [
            'stats' => [
                'leads' => (clone $leads)->count(),
                'new_leads' => (clone $leads)->where('status', 'new')->count(),
                'deals' => (clone $deals)->count(),
                'open_deals' => (clone $deals)->whereNull('won_at')->whereNull('lost_reason')->count(),
                'pipeline_value' => (clone $deals)->whereNull('won_at')->whereNull('lost_reason')->sum('value'),
                'won_deals' => (clone $deals)->whereNotNull('won_at')->count(),
            ],
            'recentLeads' => $this->scopeForCurrentUser(Lead::with('owner'))->latest()->take(5)->get(),
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

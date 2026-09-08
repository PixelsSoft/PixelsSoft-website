<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use App\Support\ScopesByOwner;
use Illuminate\Support\Facades\DB;

class CrmReportController extends Controller
{
    use ScopesByOwner;

    public function index()
    {
        $pipeline = Pipeline::where('is_default', true)->with('stages')->first();
        $leads = $this->scopeForCurrentUser(Lead::query());
        $deals = $this->scopeForCurrentUser(Deal::query());

        $dealsByStage = $pipeline
            ? $this->scopeForCurrentUser(Deal::query())
                ->select('stage_id', DB::raw('count(*) as count'), DB::raw('sum(value) as value'))
                ->where('pipeline_id', $pipeline->id)
                ->groupBy('stage_id')
                ->get()
                ->keyBy('stage_id')
            : collect();

        return view('admin.crm.reports.index', [
            'leadsBySource' => (clone $leads)->select('source', DB::raw('count(*) as count'))->groupBy('source')->get(),
            'leadsByStatus' => (clone $leads)->select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            'conversionRate' => $this->conversionRate(),
            'pipeline' => $pipeline,
            'dealsByStage' => $dealsByStage,
            'wonValue' => (clone $deals)->whereNotNull('won_at')->sum('value'),
            'lostCount' => (clone $deals)->whereNotNull('lost_reason')->count(),
        ]);
    }

    private function conversionRate(): float
    {
        $total = $this->scopeForCurrentUser(Lead::query())->count();
        if ($total === 0) {
            return 0;
        }

        return round(($this->scopeForCurrentUser(Lead::query())->where('status', 'converted')->count() / $total) * 100, 1);
    }
}

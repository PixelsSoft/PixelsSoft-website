<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use Illuminate\Support\Facades\DB;

class CrmReportController extends Controller
{
    public function index()
    {
        $pipeline = Pipeline::where('is_default', true)->with('stages')->first();

        $dealsByStage = $pipeline
            ? Deal::select('stage_id', DB::raw('count(*) as count'), DB::raw('sum(value) as value'))
                ->where('pipeline_id', $pipeline->id)
                ->groupBy('stage_id')
                ->get()
                ->keyBy('stage_id')
            : collect();

        return view('admin.crm.reports.index', [
            'leadsBySource' => Lead::select('source', DB::raw('count(*) as count'))->groupBy('source')->get(),
            'leadsByStatus' => Lead::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            'conversionRate' => $this->conversionRate(),
            'pipeline' => $pipeline,
            'dealsByStage' => $dealsByStage,
            'wonValue' => Deal::whereNotNull('won_at')->sum('value'),
            'lostCount' => Deal::whereNotNull('lost_reason')->count(),
        ]);
    }

    private function conversionRate(): float
    {
        $total = Lead::count();
        if ($total === 0) {
            return 0;
        }

        return round((Lead::where('status', 'converted')->count() / $total) * 100, 1);
    }
}

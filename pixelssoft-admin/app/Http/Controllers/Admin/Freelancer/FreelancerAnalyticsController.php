<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerPortfolioLink;
use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FreelancerAnalyticsController extends Controller
{
    public function index(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        [$from, $to] = $this->range($request);

        $projects = FreelancerProject::query()->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))->whereBetween('created_at', [$from, $to]);
        $bids = FreelancerBid::query()->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))->whereBetween('created_at', [$from, $to]);

        $submitted = (clone $bids)->where('status', 'submitted')->where('is_dry_run', false);
        $withinTarget = (clone $submitted)->whereNotNull('total_time_to_bid_ms')->get()->filter(fn ($bid) => $bid->total_time_to_bid_ms <= (($account->settings?->max_bid_submission_seconds ?? 60) * 1000));
        $submittedCount = (clone $submitted)->count();
        $acceptedCount = (clone $bids)->where('status', 'accepted')->count();

        $metrics = [
            'discovered' => (clone $projects)->count(),
            'qualified' => (clone $projects)->where('matched', true)->count(),
            'rejected' => (clone $projects)->where('automation_status', 'rejected')->count(),
            'submitted' => $submittedCount,
            'accepted' => $acceptedCount,
            'failed' => (clone $bids)->where('status', 'failed')->count(),
            'avg_bid' => round((float) (clone $submitted)->avg('amount'), 2),
            'avg_match' => round((float) (clone $projects)->where('matched', true)->avg('match_score'), 2),
            'avg_budget' => round((float) (clone $projects)->avg('budget_max'), 2),
            'avg_time_to_bid_ms' => round((float) (clone $submitted)->avg('total_time_to_bid_ms')),
            'fastest_bid_ms' => (clone $submitted)->min('total_time_to_bid_ms'),
            'slowest_bid_ms' => (clone $submitted)->max('total_time_to_bid_ms'),
            'bids_within_target' => $withinTarget->count(),
            'bids_over_target' => max(0, $submittedCount - $withinTarget->count()),
            'avg_ai_time_ms' => round((float) (clone $bids)->avg('ai_processing_time_ms')),
            'avg_api_time_ms' => round((float) (clone $bids)->avg('api_response_time_ms')),
            'avg_queue_time_ms' => round((float) (clone $bids)->avg('queue_processing_time_ms')),
            'win_rate' => $submittedCount > 0 ? round(($acceptedCount / $submittedCount) * 100, 1) : 0,
            'within_target_percent' => $submittedCount > 0 ? round(($withinTarget->count() / $submittedCount) * 100, 1) : 0,
        ];

        $byCountry = (clone $projects)->selectRaw('country, count(*) as projects, sum(matched) as qualified')->groupBy('country')->orderByDesc('projects')->limit(15)->get();
        $byStrategy = (clone $bids)->with('strategy')->get()->groupBy('strategy_id')->map(function ($group) {
            $submitted = $group->where('status', 'submitted')->where('is_dry_run', false);
            $accepted = $group->where('status', 'accepted');
            return [
                'name' => $group->first()?->strategy?->name ?? 'No strategy',
                'bids' => $submitted->count(),
                'wins' => $accepted->count(),
                'avg_bid' => round((float) $submitted->avg('amount'), 2),
                'avg_match' => round((float) $group->avg('match_score'), 2),
                'win_rate' => $submitted->count() ? round(($accepted->count() / $submitted->count()) * 100, 1) : 0,
            ];
        })->values();

        $byAi = (clone $bids)->selectRaw('ai_provider, ai_model, count(*) as total')->groupBy('ai_provider', 'ai_model')->orderByDesc('total')->get();
        $portfolioUsage = FreelancerPortfolioLink::query()->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))->with('skills')->orderByDesc('usage_count')->limit(20)->get();
        $failures = (clone $bids)->where('status', 'failed')->selectRaw('error_message, count(*) as total')->groupBy('error_message')->orderByDesc('total')->limit(20)->get();

        return view('admin.freelancer.analytics.index', compact('account', 'metrics', 'byCountry', 'byStrategy', 'byAi', 'portfolioUsage', 'failures', 'from', 'to'));
    }

    protected function range(Request $request): array
    {
        $preset = $request->string('range')->toString() ?: '7d';
        $to = now()->endOfDay();
        $from = match ($preset) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            'month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'custom' => Carbon::parse($request->input('from', now()->subDays(7)))->startOfDay(),
            default => now()->subDays(7)->startOfDay(),
        };
        if ($preset === 'yesterday') {
            $to = now()->subDay()->endOfDay();
        }
        if ($preset === 'last_month') {
            $to = now()->subMonth()->endOfMonth();
        }
        if ($preset === 'custom' && $request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
        }

        return [$from, $to];
    }
}

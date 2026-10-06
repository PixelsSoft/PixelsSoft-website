<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Support\Facades\Cache;

class FreelancerDashboardController extends Controller
{
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        $tz = $account?->timezone ?: config('freelancer.timezone');
        $today = now($tz)->startOfDay();
        $week = now($tz)->startOfWeek();
        $month = now($tz)->startOfMonth();

        $stats = Cache::remember('freelancer.analytics.dashboard.'.($account?->id ?? 0), 60, function () use ($account, $today, $week, $month) {
            $projects = FreelancerProject::query()->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id));
            $bids = FreelancerBid::query()->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id));

            $submittedCount = (clone $bids)->where('status', 'submitted')->where('is_dry_run', false)->count();
            $acceptedCount = (clone $bids)->where('status', 'accepted')->count();

            return [
                'projects_today' => (clone $projects)->where('created_at', '>=', $today)->count(),
                'qualified' => (clone $projects)->where('automation_status', 'qualified')->count(),
                'rejected' => (clone $projects)->where('automation_status', 'rejected')->count(),
                'bids_today' => (clone $bids)->where('status', 'submitted')->where('is_dry_run', false)->where('submitted_at', '>=', $today)->count(),
                'bids_week' => (clone $bids)->where('status', 'submitted')->where('is_dry_run', false)->where('submitted_at', '>=', $week)->count(),
                'bids_month' => (clone $bids)->where('status', 'submitted')->where('is_dry_run', false)->where('submitted_at', '>=', $month)->count(),
                'successful' => $acceptedCount,
                'failed' => (clone $bids)->where('status', 'failed')->count(),
                'pending' => (clone $bids)->whereIn('status', ['pending', 'queued', 'processing'])->count(),
                'avg_bid' => round((float) (clone $bids)->where('status', 'submitted')->where('is_dry_run', false)->avg('amount'), 2),
                'avg_match' => round((float) (clone $projects)->where('matched', true)->avg('match_score'), 2),
                'win_rate' => $submittedCount > 0 ? round(($acceptedCount / $submittedCount) * 100, 1) : 0,
            ];
        });

        $recent = FreelancerProject::query()
            ->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))
            ->latest('posted_at')
            ->limit(10)
            ->get();

        return view('admin.freelancer.dashboard', [
            'account' => $account,
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}

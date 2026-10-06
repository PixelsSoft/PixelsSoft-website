<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Jobs\Freelancer\SyncFreelancerPortfolioJob;
use App\Models\Freelancer\FreelancerPortfolio;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;

class FreelancerPortfolioController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $portfolios = $account->portfolios()->latest()->paginate(30);

        return view('admin.freelancer.portfolio.index', compact('account', 'portfolios'));
    }

    public function update(Request $request, FreelancerPortfolio $portfolio)
    {
        $this->ensurePortfolioOwned($portfolio);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_enabled' => ['nullable', 'boolean'],
            'use_for_bidding' => ['nullable', 'boolean'],
        ]);

        $portfolio->fill([
            'is_enabled' => $request->boolean('is_enabled'),
            'use_for_bidding' => $request->boolean('use_for_bidding'),
            'title' => $data['title'] ?? $portfolio->title,
            'description' => $data['description'] ?? $portfolio->description,
        ])->save();

        return back()->with('success', 'Portfolio item updated.');
    }

    public function sync(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        if (!$account?->is_connected) {
            return back()->with('error', 'Connect account first.');
        }
        SyncFreelancerPortfolioJob::dispatch($account->id);

        return back()->with('success', 'Portfolio sync queued.');
    }
}

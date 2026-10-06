<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Models\Freelancer\FreelancerStrategy;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;

class FreelancerStrategyController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $strategies = $account->strategies()->orderBy('priority')->get();

        return view('admin.freelancer.strategies.index', compact('account', 'strategies'));
    }

    public function store(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $data = $this->validated($request);
        $strategy = $account->strategies()->create($data);

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $account->id,
            'action' => 'strategy.created',
            'entity_type' => FreelancerStrategy::class,
            'entity_id' => $strategy->id,
            'new_value' => $data,
        ]);

        return back()->with('success', 'Strategy created.');
    }

    public function update(Request $request, FreelancerStrategy $strategy)
    {
        $this->ensureStrategyOwned($strategy);
        $data = $this->validated($request);
        $old = $strategy->only(array_keys($data));
        $strategy->fill($data)->save();

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $strategy->freelancer_account_id,
            'action' => 'strategy.updated',
            'entity_type' => FreelancerStrategy::class,
            'entity_id' => $strategy->id,
            'old_value' => $old,
            'new_value' => $data,
        ]);

        return back()->with('success', 'Strategy updated.');
    }

    public function destroy(FreelancerStrategy $strategy)
    {
        $this->ensureStrategyOwned($strategy);
        $strategy->delete();

        return back()->with('success', 'Strategy deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['required', 'integer', 'min:1', 'max:100'],
            'min_skill_match_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'min_client_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'max_project_age_minutes' => ['nullable', 'integer', 'min:1'],
            'max_bid_count' => ['nullable', 'integer', 'min:0'],
            'bid_delay_seconds' => ['nullable', 'integer', 'min:0'],
            'daily_limit' => ['nullable', 'integer', 'min:0'],
            'bid_amount_mode' => ['required', 'string', 'max:40'],
            'bid_fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'bid_percent' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:365'],
            'timezone' => ['nullable', 'timezone'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

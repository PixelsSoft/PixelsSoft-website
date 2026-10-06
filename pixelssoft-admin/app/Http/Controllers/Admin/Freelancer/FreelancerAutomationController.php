<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;

class FreelancerAutomationController extends Controller
{
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();

        return view('admin.freelancer.automation.index', compact('account'));
    }

    public function update(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();

        $data = $request->validate([
            'automation_enabled' => ['nullable', 'boolean'],
            'global_paused' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
            'automation_mode' => ['required', 'in:manual,approval,automatic'],
            'timezone' => ['required', 'timezone'],
            'daily_bid_limit' => ['required', 'integer', 'min:0', 'max:1000'],
            'hourly_bid_limit' => ['required', 'integer', 'min:0', 'max:100'],
            'monthly_bid_limit' => ['required', 'integer', 'min:0', 'max:10000'],
            'bid_delay_seconds' => ['required', 'integer', 'in:0,30,60,120,300,600'],
        ]);

        $old = $account->only(array_keys($data));
        $account->fill([
            'automation_enabled' => $request->boolean('automation_enabled'),
            'global_paused' => $request->boolean('global_paused'),
            'dry_run' => $request->boolean('dry_run'),
            'automation_mode' => $data['automation_mode'],
            'timezone' => $data['timezone'],
            'daily_bid_limit' => $data['daily_bid_limit'],
            'hourly_bid_limit' => $data['hourly_bid_limit'],
            'monthly_bid_limit' => $data['monthly_bid_limit'],
            'bid_delay_seconds' => $data['bid_delay_seconds'],
        ])->save();

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $account->id,
            'action' => 'automation.updated',
            'entity_type' => $account::class,
            'entity_id' => $account->id,
            'old_value' => $old,
            'new_value' => $account->only(array_keys($data)),
        ]);

        return back()->with('success', 'Automation settings saved.');
    }

    public function pause(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $account->fill([
            'global_paused' => true,
            'automation_enabled' => false,
        ])->save();

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $account->id,
            'action' => 'automation.emergency_pause',
            'entity_type' => $account::class,
            'entity_id' => $account->id,
        ]);

        return back()->with('success', 'Emergency pause enabled — no bids will be submitted.');
    }
}

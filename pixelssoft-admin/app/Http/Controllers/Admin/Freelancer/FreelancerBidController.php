<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Models\Freelancer\FreelancerBid;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;

class FreelancerBidController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        $bids = FreelancerBid::query()
            ->with(['project', 'strategy'])
            ->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))
            ->latest()
            ->paginate(25);

        return view('admin.freelancer.bids.index', compact('bids', 'account'));
    }

    public function cancel(FreelancerBid $bid)
    {
        $this->ensureBidOwned($bid);

        if (in_array($bid->status, ['submitted', 'accepted'], true) && ! $bid->is_dry_run) {
            return back()->with('error', 'Cannot cancel an already submitted bid from CRM. Retract via Freelancer if needed.');
        }

        $bid->fill(['status' => 'cancelled'])->save();

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $bid->freelancer_account_id,
            'action' => 'bid.cancelled',
            'entity_type' => FreelancerBid::class,
            'entity_id' => $bid->id,
        ]);

        return back()->with('success', 'Bid cancelled.');
    }
}

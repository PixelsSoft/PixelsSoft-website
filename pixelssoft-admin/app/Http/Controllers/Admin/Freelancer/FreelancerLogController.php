<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerApiLog;
use App\Services\Freelancer\FreelancerAccountResolver;

class FreelancerLogController extends Controller
{
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        $logs = FreelancerApiLog::query()
            ->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))
            ->latest()
            ->paginate(50);

        return view('admin.freelancer.logs.index', compact('logs', 'account'));
    }
}

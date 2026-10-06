<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerAutomationLog;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerProject;

class FreelancerAutomationLogService
{
    public function log(
        string $stage,
        string $message,
        ?FreelancerAccount $account = null,
        ?FreelancerProject $project = null,
        ?FreelancerBid $bid = null,
        array $context = [],
        string $level = 'info'
    ): FreelancerAutomationLog {
        return FreelancerAutomationLog::create([
            'freelancer_account_id' => $account?->id ?? $project?->freelancer_account_id ?? $bid?->freelancer_account_id,
            'freelancer_project_id' => $project?->id ?? $bid?->freelancer_project_id,
            'freelancer_bid_id' => $bid?->id,
            'stage' => $stage,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }
}

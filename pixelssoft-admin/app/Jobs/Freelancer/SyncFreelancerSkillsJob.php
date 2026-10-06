<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Services\Freelancer\FreelancerSkillService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFreelancerSkillsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $accountId) {}

    public function handle(FreelancerSkillService $skills): void
    {
        $account = FreelancerAccount::with('skills')->find($this->accountId);
        if (!$account) {
            return;
        }

        $ids = $account->skills->pluck('freelancer_skill_id')->filter()->all();
        if ($ids) {
            $skills->syncCatalog($account, $ids);
        }
    }
}

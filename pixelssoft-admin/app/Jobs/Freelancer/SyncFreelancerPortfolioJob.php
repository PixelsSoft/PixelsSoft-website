<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Services\Freelancer\FreelancerPortfolioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFreelancerPortfolioJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $accountId) {}

    public function handle(FreelancerPortfolioService $portfolios): void
    {
        $account = FreelancerAccount::find($this->accountId);
        if (!$account || !$account->is_connected) {
            return;
        }

        $portfolios->sync($account);
    }
}

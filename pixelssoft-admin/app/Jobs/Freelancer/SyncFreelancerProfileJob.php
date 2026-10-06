<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Services\Freelancer\FreelancerProfileService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncFreelancerProfileJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(public int $accountId) {}

    public function backoff(): array
    {
        return [0, 30, 120, 300];
    }

    public function handle(FreelancerProfileService $profiles): void
    {
        $account = FreelancerAccount::find($this->accountId);
        if (!$account || !$account->is_connected) {
            return;
        }

        $profiles->sync($account);
    }

    public function failed(?Throwable $e): void
    {
        $account = FreelancerAccount::find($this->accountId);
        $account?->forceFill([
            'last_api_error_at' => now(),
            'last_api_error' => $e ? substr($e->getMessage(), 0, 500) : 'Profile sync failed',
        ])->save();
    }
}

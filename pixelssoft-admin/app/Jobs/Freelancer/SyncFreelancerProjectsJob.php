<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Services\Freelancer\FreelancerProjectService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncFreelancerProjectsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(public int $accountId, public array $options = []) {}

    public function backoff(): array
    {
        return [0, 30, 120, 300];
    }

    public function handle(FreelancerProjectService $projects): void
    {
        $account = FreelancerAccount::find($this->accountId);
        if (!$account || !$account->is_connected) {
            return;
        }

        $projects->syncActiveProjects($account, $this->options);
    }

    public function failed(?Throwable $e): void
    {
        $account = FreelancerAccount::find($this->accountId);
        $account?->forceFill([
            'last_api_error_at' => now(),
            'last_api_error' => $e ? substr($e->getMessage(), 0, 500) : 'Project sync failed',
        ])->save();
    }
}

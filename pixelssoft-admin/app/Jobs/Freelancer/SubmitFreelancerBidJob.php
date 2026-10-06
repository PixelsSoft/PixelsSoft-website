<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\FreelancerAutomationLogService;
use App\Services\Freelancer\FreelancerBidService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubmitFreelancerBidJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public int $uniqueFor = 3600;

    public function __construct(public int $projectId, public bool $manual = false, public ?int $userId = null) {}

    public function uniqueId(): string
    {
        return 'freelancer-bid-'.$this->projectId;
    }

    public function backoff(): array
    {
        return [0, 15, 60, 180];
    }

    public function handle(FreelancerBidService $bids, FreelancerAutomationLogService $logger): void
    {
        $project = FreelancerProject::with(['account.settings', 'strategy'])->find($this->projectId);
        if (!$project || !$project->account) {
            return;
        }

        $account = $project->account;
        if ($account->global_paused || !$account->is_connected || $project->hasSubmittedBid()) {
            return;
        }

        try {
            $bids->submit($account, $project, $this->userId, $this->manual);
        } catch (Throwable $e) {
            FreelancerBid::updateOrCreate(
                [
                    'freelancer_account_id' => $account->id,
                    'freelancer_project_id' => $project->id,
                ],
                [
                    'status' => 'failed',
                    'error_message' => substr($e->getMessage(), 0, 1000),
                    'is_dry_run' => (bool) $account->dry_run,
                ]
            );
            $project->fill(['bid_status' => 'failed'])->save();
            $logger->log('bid_failed', 'Bid submission failed', $account, $project, null, ['message' => $e->getMessage()], 'error');

            if ($this->shouldRetry($e)) {
                throw $e;
            }

            $this->fail($e);
        }
    }

    public function failed(?Throwable $e): void
    {
        $project = FreelancerProject::find($this->projectId);
        $project?->fill(['bid_status' => 'failed'])->save();
    }

    protected function shouldRetry(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'rate limit')
            || str_contains($message, 'connection failed')
            || str_contains($message, 'timed out')
            || str_contains($message, 'http 429')
            || str_contains($message, 'http 502')
            || str_contains($message, 'http 503')
            || str_contains($message, 'http 504');
    }
}

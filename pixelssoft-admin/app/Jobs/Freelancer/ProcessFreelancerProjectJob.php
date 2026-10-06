<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\FreelancerAutomationLogService;
use App\Services\Freelancer\FreelancerBidService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessFreelancerProjectJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $uniqueFor = 300;

    public function __construct(public int $projectId) {}

    public function uniqueId(): string
    {
        return 'freelancer-process-'.$this->projectId;
    }

    public function backoff(): array
    {
        return [0, 15, 60];
    }

    public function handle(FreelancerBidService $bids, FreelancerAutomationLogService $logger): void
    {
        $project = FreelancerProject::with(['account.settings', 'strategy'])->find($this->projectId);
        if (!$project || !$project->account) {
            return;
        }

        if (in_array($project->bid_status, ['queued', 'processing', 'submitted'], true)) {
            return;
        }

        if (in_array($project->automation_status, ['processing', 'queued', 'bid'], true)) {
            return;
        }

        $account = $project->account;
        $project->forceFill([
            'automation_status' => 'processing',
            'matching_started_at' => $project->matching_started_at ?: now(),
        ])->save();
        $logger->log('matching_started', 'Skill matching started', $account, $project);

        $prepared = $bids->prepare($account, $project, $project->strategy);
        $project->refresh();

        if ($project->automation_status !== 'qualified') {
            $logger->log('project_rejected', 'Project rejected by matching rules', $account, $project, null, ['reason' => $project->reject_reason], 'warning');
            return;
        }

        $logger->log('project_qualified', 'Project qualified', $account, $project, null, ['match_score' => $project->match_score]);
        $logger->log('strategy_selected', 'Strategy selected', $account, $project, null, ['strategy' => $prepared['strategy']?->name]);
        $logger->log('portfolio_selected', 'Portfolio links selected', $account, $project, null, ['portfolio_ids' => $prepared['portfolioLinkIds']]);

        GenerateFreelancerProposalJob::dispatch($project->id);
    }

    public function failed(?Throwable $e): void
    {
        FreelancerProject::whereKey($this->projectId)->update([
            'reject_reason' => $e ? substr($e->getMessage(), 0, 250) : 'Processing failed',
        ]);
    }
}

<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\AiProposalService;
use App\Services\Freelancer\FreelancerAutomationLogService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFreelancerProposalJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $uniqueFor = 300;

    public function __construct(public int $projectId) {}

    public function uniqueId(): string
    {
        return 'freelancer-proposal-'.$this->projectId;
    }

    public function handle(AiProposalService $ai, FreelancerAutomationLogService $logger): void
    {
        $project = FreelancerProject::with(['account.settings', 'strategy'])->find($this->projectId);
        if (!$project?->account || $project->automation_status !== 'qualified') {
            return;
        }

        $result = $ai->generate($project->account, $project, $project->strategy);
        $suggested = $project->suggested ?: [];
        $suggested['amount'] = $result['suggested_bid'] ?? $suggested['amount'] ?? null;
        $suggested['delivery_days'] = $result['suggested_delivery'] ?? $suggested['delivery_days'] ?? null;
        $suggested['proposal'] = $result['proposal'] ?? $suggested['proposal'] ?? null;
        $suggested['portfolio_ids'] = $result['portfolio_link_ids'] ?? $suggested['portfolio_ids'] ?? [];
        $project->fill(['suggested' => $suggested])->save();

        $logger->log('ai_completed', 'Proposal stage completed', $project->account, $project, null, ['mode' => $result['mode'] ?? 'template', 'provider' => $result['provider'] ?? null]);

        if ($project->account->automation_mode === 'automatic' && $project->account->canAutomate()) {
            $delay = (int) ($project->strategy?->bid_delay_seconds ?? $project->account->bid_delay_seconds ?? 0);
            $project->forceFill(['bid_queued_at' => now()])->save();
            $logger->log('bid_queued', 'Bid queued for submission', $project->account, $project, null, ['delay_seconds' => $delay]);
            SubmitFreelancerBidJob::dispatch($project->id)->delay(now()->addSeconds(max(0, $delay)));
            $project->fill(['automation_status' => 'queued', 'bid_status' => 'queued'])->save();
        } elseif (
            $project->account->automation_mode === 'approval'
            || ($result['mode'] ?? null) === 'manual_approval'
        ) {
            $project->fill(['automation_status' => 'qualified', 'bid_status' => 'approval_required'])->save();
            $logger->log('approval_required', 'Proposal ready for manual approval', $project->account, $project);
        }
    }
}

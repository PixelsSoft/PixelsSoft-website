<?php

namespace Tests\Feature\Freelancer;

use App\Jobs\Freelancer\GenerateFreelancerProposalJob;
use App\Jobs\Freelancer\ProcessFreelancerProjectJob;
use App\Jobs\Freelancer\SubmitFreelancerBidJob;
use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Services\Freelancer\FreelancerSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelancerWorkflowTimingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_workflow_records_timing_and_logs(): void
    {
        $account = FreelancerAccount::factory()->create([
            'dry_run' => true,
            'automation_enabled' => true,
            'automation_mode' => 'automatic',
            'bid_delay_seconds' => 0,
        ]);
        $settings = app(FreelancerSettingsService::class)->forAccount($account);
        $settings->update(['proposal_mode' => 'template', 'max_bid_submission_seconds' => 60]);

        FreelancerSkill::factory()->create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 1,
            'name' => 'PHP',
        ]);

        $project = FreelancerProject::factory()->create([
            'freelancer_account_id' => $account->id,
            'required_skills' => [['id' => 1, 'name' => 'PHP']],
            'detected_at' => now()->subSeconds(15),
            'automation_status' => 'new',
            'bid_status' => 'none',
        ]);

        ProcessFreelancerProjectJob::dispatchSync($project->id);
        GenerateFreelancerProposalJob::dispatchSync($project->id);
        SubmitFreelancerBidJob::dispatchSync($project->id, true, null);

        $project->refresh();
        $bid = $project->bids()->latest()->first();

        $this->assertNotNull($project->matching_started_at);
        $this->assertNotNull($project->matching_completed_at);
        $this->assertNotNull($project->bid_started_at);
        $this->assertNotNull($project->total_processing_time_ms);
        $this->assertNotNull($bid);
        $this->assertTrue($bid->is_dry_run);
        $this->assertGreaterThan(0, $project->automationLogs()->count());
    }
}

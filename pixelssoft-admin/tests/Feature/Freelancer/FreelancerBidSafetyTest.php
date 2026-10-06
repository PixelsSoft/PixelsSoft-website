<?php

namespace Tests\Feature\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Services\Freelancer\FreelancerBidService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelancerBidSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_submit_real_bid(): void
    {
        $account = FreelancerAccount::create([
            'is_connected' => true,
            'automation_enabled' => true,
            'dry_run' => true,
            'global_paused' => false,
            'automation_mode' => 'automatic',
            'freelancer_user_id' => 123,
            'access_token' => 'token',
            'daily_bid_limit' => 20,
            'hourly_bid_limit' => 5,
            'monthly_bid_limit' => 500,
            'timezone' => 'Asia/Karachi',
        ]);

        FreelancerSkill::create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 5,
            'name' => 'Flutter',
            'automation_enabled' => true,
        ]);

        $project = FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 999,
            'title' => 'Test project',
            'description' => 'Build an app',
            'budget_min' => 200,
            'budget_max' => 400,
            'currency' => 'USD',
            'project_type' => 'fixed',
            'status' => 'active',
            'required_skills' => [['id' => 5, 'name' => 'Flutter']],
            'automation_status' => 'qualified',
            'bid_status' => 'none',
            'posted_at' => now(),
        ]);

        $bid = app(FreelancerBidService::class)->submit($account, $project, null, true);

        $this->assertTrue($bid->is_dry_run);
        $this->assertSame('cancelled', $bid->status);
        $this->assertStringContainsString('DRY RUN', (string) $bid->error_message);
        $this->assertNull($bid->freelancer_bid_id);
    }

    public function test_global_pause_blocks_submission(): void
    {
        $account = FreelancerAccount::create([
            'is_connected' => true,
            'automation_enabled' => true,
            'dry_run' => false,
            'global_paused' => true,
            'access_token' => 'token',
            'timezone' => 'Asia/Karachi',
        ]);

        $project = FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 1000,
            'title' => 'Paused project',
            'budget_min' => 100,
            'automation_status' => 'qualified',
            'bid_status' => 'none',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('paused');
        app(FreelancerBidService::class)->submit($account, $project, null, true);
    }

    public function test_duplicate_bid_protection(): void
    {
        $account = FreelancerAccount::create([
            'is_connected' => true,
            'automation_enabled' => true,
            'dry_run' => true,
            'global_paused' => false,
            'access_token' => 'token',
            'freelancer_user_id' => 1,
            'timezone' => 'Asia/Karachi',
            'daily_bid_limit' => 20,
            'hourly_bid_limit' => 5,
            'monthly_bid_limit' => 500,
        ]);

        $project = FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 1001,
            'title' => 'Dup',
            'budget_min' => 100,
            'automation_status' => 'qualified',
            'bid_status' => 'none',
            'posted_at' => now(),
        ]);

        FreelancerBid::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => $project->id,
            'status' => 'submitted',
            'is_dry_run' => false,
            'amount' => 100,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('duplicate');
        app(FreelancerBidService::class)->submit($account, $project, null, true);
    }
}

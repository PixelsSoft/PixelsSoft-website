<?php

namespace Tests\Feature\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Services\Freelancer\FreelancerBidService;
use App\Services\Freelancer\FreelancerMatchingService;
use App\Services\Freelancer\FreelancerSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelancerScheduleAndSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_blocks_bid_simulation_outside_allowed_window(): void
    {
        $account = FreelancerAccount::factory()->create([
            'schedule' => [
                ['days' => [1], 'start' => '09:00', 'end' => '10:00', 'enabled' => true],
            ],
            'timezone' => 'UTC',
        ]);
        app(FreelancerSettingsService::class)->forAccount($account);

        FreelancerSkill::factory()->create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 11,
            'name' => 'Laravel',
        ]);

        $project = FreelancerProject::factory()->create([
            'freelancer_account_id' => $account->id,
            'required_skills' => [['id' => 11, 'name' => 'Laravel']],
            'posted_at' => now('UTC')->subMinutes(5),
        ]);

        $blockedAt = now('UTC')->startOfWeek()->setTime(12, 0);
        $matching = app(FreelancerMatchingService::class);
        $this->assertFalse($matching->isWithinSchedule($account, null, $blockedAt));

        $account->update(['schedule' => [['days' => [1], 'start' => '00:00', 'end' => '01:00', 'enabled' => true]]]);
        $simulation = app(FreelancerBidService::class)->simulate($account->fresh(), $project->fresh(), false);
        $this->assertFalse($simulation['ready']);
        $this->assertStringContainsString('schedule', strtolower($simulation['final_reason']));
    }
}

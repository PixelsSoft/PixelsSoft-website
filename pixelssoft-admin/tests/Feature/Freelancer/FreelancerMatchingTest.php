<?php

namespace Tests\Feature\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Models\User;
use App\Services\Freelancer\FreelancerMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class FreelancerMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_match_percentage_and_negative_keyword_rejection(): void
    {
        $account = FreelancerAccount::create([
            'timezone' => 'Asia/Karachi',
            'min_skill_match_percent' => 60,
            'negative_keywords' => ['wordpress', 'seo'],
            'positive_keywords' => ['react native', 'firebase'],
            'min_positive_keywords' => 1,
            'country_mode' => 'all',
            'project_type_filter' => 'both',
        ]);

        FreelancerSkill::create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 1,
            'name' => 'React Native',
            'automation_enabled' => true,
        ]);
        FreelancerSkill::create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 2,
            'name' => 'Firebase',
            'automation_enabled' => true,
        ]);

        $project = FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 1001,
            'title' => 'React Native Firebase app',
            'description' => 'Need a React Native + Firebase mobile app',
            'project_type' => 'fixed',
            'budget_min' => 500,
            'budget_max' => 1500,
            'required_skills' => [
                ['id' => 1, 'name' => 'React Native'],
                ['id' => 2, 'name' => 'Firebase'],
            ],
            'posted_at' => now()->subMinutes(3),
            'automation_status' => 'new',
            'bid_status' => 'none',
        ]);

        $matching = app(FreelancerMatchingService::class);
        $result = $matching->evaluate($account, $project);

        $this->assertTrue($result['qualified']);
        $this->assertEquals(100.0, $result['skill_match_score']);

        $rejected = FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 1002,
            'title' => 'WordPress SEO package',
            'description' => 'Need WordPress SEO work',
            'project_type' => 'fixed',
            'budget_min' => 100,
            'budget_max' => 200,
            'required_skills' => [['id' => 1, 'name' => 'React Native']],
            'posted_at' => now(),
            'automation_status' => 'new',
            'bid_status' => 'none',
        ]);

        $bad = $matching->evaluate($account, $rejected);
        $this->assertFalse($bad['qualified']);
        $this->assertStringContainsString('Excluded keyword', (string) $bad['reject_reason']);
    }

    public function test_access_token_is_encrypted_and_hidden(): void
    {
        $account = FreelancerAccount::create([
            'access_token' => 'plain-secret-token',
            'refresh_token' => 'plain-refresh-token',
            'is_connected' => true,
        ]);

        $this->assertNotEquals('plain-secret-token', $account->getAttributes()['access_token']);
        $this->assertEquals('plain-secret-token', Crypt::decryptString($account->getAttributes()['access_token']));
        $this->assertArrayNotHasKey('access_token', $account->toArray());
        $this->assertEquals('plain-secret-token', $account->plainAccessToken());
    }

    public function test_duplicate_project_unique_constraint(): void
    {
        $account = FreelancerAccount::create([]);
        FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 55,
            'title' => 'A',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        FreelancerProject::create([
            'freelancer_account_id' => $account->id,
            'freelancer_project_id' => 55,
            'title' => 'B',
        ]);
    }
}

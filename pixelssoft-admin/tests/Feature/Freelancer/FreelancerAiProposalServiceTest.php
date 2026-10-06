<?php

namespace Tests\Feature\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolioLink;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Services\Freelancer\AiProposalService;
use App\Services\Freelancer\FreelancerSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FreelancerAiProposalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_provider_generates_proposal_from_real_config_shape(): void
    {
        Http::fake([
            'https://api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'proposal' => 'Hello client, here is a tailored proposal with https://portfolio.example/app-one',
                            'suggested_bid' => 225,
                            'suggested_delivery_days' => 5,
                            'selected_portfolio_urls' => ['https://portfolio.example/app-one'],
                            'reasoning' => 'Good skill alignment',
                            'confidence_score' => 91,
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $account = FreelancerAccount::factory()->create(['dry_run' => true]);
        $settings = app(FreelancerSettingsService::class)->forAccount($account);
        $settings->fill([
            'proposal_mode' => 'ai',
            'ai_enabled' => true,
            'ai_proposal_enabled' => true,
            'ai_provider' => 'openai',
            'ai_api_key' => 'secret-key',
            'ai_model' => 'gpt-test',
            'proposal_max_characters' => 1000,
        ])->save();

        $skill = FreelancerSkill::factory()->create([
            'freelancer_account_id' => $account->id,
            'freelancer_skill_id' => 101,
            'name' => 'Laravel',
        ]);
        $link = FreelancerPortfolioLink::factory()->create([
            'freelancer_account_id' => $account->id,
            'title' => 'App One',
            'url' => 'https://portfolio.example/app-one',
        ]);
        $link->skills()->sync([$skill->id]);

        $project = FreelancerProject::factory()->create([
            'freelancer_account_id' => $account->id,
            'required_skills' => [['id' => 101, 'name' => 'Laravel']],
            'description' => 'Need a Laravel application with API integration.',
        ]);

        $result = app(AiProposalService::class)->generate($account->fresh(), $project->fresh());

        $this->assertSame('Hello client, here is a tailored proposal with https://portfolio.example/app-one', $result['proposal']);
        $this->assertSame(225, $result['suggested_bid']);
        $this->assertSame(5, $result['suggested_delivery']);
        $this->assertSame([$link->id], $result['portfolio_link_ids']);
        $this->assertSame('openai', $result['provider']);
    }
}

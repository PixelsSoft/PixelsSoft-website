<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerStrategy;
use App\Services\Freelancer\Ai\AiProviderInterface;
use App\Services\Freelancer\Ai\AnthropicProvider;
use App\Services\Freelancer\Ai\GeminiProvider;
use App\Services\Freelancer\Ai\OpenAiCompatibleProvider;
use App\Services\Freelancer\Ai\OpenAiProvider;
use Illuminate\Support\Arr;
use RuntimeException;

class AiProposalService
{
    public function __construct(
        protected FreelancerBidService $bids,
        protected FreelancerPortfolioLinkService $portfolioLinks,
        protected FreelancerSettingsService $settingsService,
        protected FreelancerAutomationLogService $logger,
    ) {}

    public function generate(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy = null): array
    {
        $account->loadMissing(['settings', 'skills', 'portfolioLinks.skills', 'portfolioLinks.categories']);
        $settings = $this->settingsService->forAccount($account);
        $prepared = $this->bids->prepare($account, $project, $strategy);

        if (!$settings->ai_enabled || !$settings->ai_proposal_enabled || !in_array($settings->proposal_mode, ['ai', 'hybrid'], true)) {
            return $this->templateFallback($account, $project, $prepared, 'AI disabled or template mode active');
        }

        $provider = $this->provider($settings->ai_provider);
        $payload = $this->payload($account, $project, $prepared, $settings);
        $config = $this->providerConfig($settings);

        $project->forceFill(['ai_started_at' => now()])->save();
        $aiStart = microtime(true);
        $this->logger->log('ai_started', 'AI proposal generation started', $account, $project, null, ['provider' => $settings->ai_provider, 'model' => $settings->ai_model]);

        try {
            $response = $provider->analyzeProject($payload, $config);
            $elapsed = (int) round((microtime(true) - $aiStart) * 1000);
            $project->forceFill(['ai_completed_at' => now()])->save();

            $selectedUrls = collect($response['selected_portfolio_urls'] ?? [])->filter()->values();
            $selectedIds = $account->portfolioLinks()->whereIn('url', $selectedUrls->all())->pluck('id')->all();
            $selectedIds = $selectedIds ?: $prepared['portfolioLinkIds'];

            $proposal = trim((string) ($response['proposal'] ?? ''));
            if ($proposal === '') {
                throw new RuntimeException('AI returned an empty proposal.');
            }

            $proposal = mb_substr($proposal, 0, (int) $settings->proposal_max_characters);
            $this->portfolioLinks->validateProposalUrls($account, $proposal, $selectedIds);

            return [
                'proposal' => $proposal,
                'suggested_bid' => $response['suggested_bid'] ?? $prepared['amount'],
                'suggested_delivery' => $response['suggested_delivery_days'] ?? $prepared['delivery'],
                'portfolio_link_ids' => $selectedIds,
                'reasoning' => $response['reasoning'] ?? null,
                'confidence_score' => $response['confidence_score'] ?? null,
                'provider' => $settings->ai_provider,
                'model' => $settings->ai_model,
                'processing_time_ms' => $elapsed,
                'mode' => $settings->proposal_mode,
            ];
        } catch (\Throwable $e) {
            $project->forceFill(['ai_completed_at' => now()])->save();
            $settings->forceFill(['ai_status' => 'failed', 'ai_last_error' => substr($e->getMessage(), 0, 500)])->save();
            $this->logger->log('ai_failed', 'AI proposal generation failed', $account, $project, null, ['message' => $e->getMessage()], 'error');

            return match ($settings->ai_failure_behavior) {
                'manual_approval' => [
                    'proposal' => null,
                    'suggested_bid' => $prepared['amount'],
                    'suggested_delivery' => $prepared['delivery'],
                    'portfolio_link_ids' => $prepared['portfolioLinkIds'],
                    'reasoning' => $e->getMessage(),
                    'provider' => $settings->ai_provider,
                    'model' => $settings->ai_model,
                    'processing_time_ms' => 0,
                    'mode' => 'manual_approval',
                ],
                'do_not_bid' => throw $e,
                default => $this->templateFallback($account, $project, $prepared, $e->getMessage()),
            };
        }
    }

    public function testConnection(FreelancerAccount $account, array $sample): array
    {
        $settings = $this->settingsService->forAccount($account);
        $provider = $this->provider($settings->ai_provider);
        $start = microtime(true);
        $response = $provider->analyzeProject([
            'system_prompt' => $settings->ai_system_prompt ?: $this->settingsService->defaultSystemPrompt(),
            'prompt' => "Generate a JSON response for this sample Freelancer project: ".json_encode($sample),
        ], $this->providerConfig($settings));
        $elapsed = (int) round((microtime(true) - $start) * 1000);

        $settings->forceFill([
            'ai_tested_at' => now(),
            'ai_status' => 'connected',
            'ai_last_error' => null,
        ])->save();

        return $response + [
            'processing_time_ms' => $elapsed,
            'provider' => $settings->ai_provider,
            'model' => $settings->ai_model,
        ];
    }

    protected function templateFallback(FreelancerAccount $account, FreelancerProject $project, array $prepared, string $reason): array
    {
        return [
            'proposal' => $prepared['proposal'],
            'suggested_bid' => $prepared['amount'],
            'suggested_delivery' => $prepared['delivery'],
            'portfolio_link_ids' => $prepared['portfolioLinkIds'],
            'reasoning' => $reason,
            'confidence_score' => min(95, max(40, (float) ($prepared['result']['match_score'] ?? 50))),
            'provider' => null,
            'model' => null,
            'processing_time_ms' => 0,
            'mode' => 'template',
        ];
    }

    protected function provider(?string $provider): AiProviderInterface
    {
        return match ($provider) {
            'openai' => new OpenAiProvider(),
            'anthropic' => new AnthropicProvider(),
            'gemini' => new GeminiProvider(),
            'openai_compatible' => new OpenAiCompatibleProvider(),
            default => throw new RuntimeException('AI provider is not configured.'),
        };
    }

    protected function providerConfig($settings): array
    {
        return [
            'api_key' => $settings->plainAiApiKey(),
            'model' => $settings->ai_model,
            'base_url' => $settings->ai_base_url,
            'temperature' => (float) $settings->ai_temperature,
            'max_tokens' => (int) $settings->ai_max_tokens,
            'timeout' => (int) $settings->ai_timeout,
        ];
    }

    protected function payload(FreelancerAccount $account, FreelancerProject $project, array $prepared, $settings): array
    {
        $portfolioLinks = $account->portfolioLinks()->with('skills')
            ->whereIn('id', $prepared['portfolioLinkIds'])
            ->get()
            ->map(fn ($link) => [
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'skills' => $link->skills->pluck('name')->values()->all(),
            ])->values()->all();

        $body = [
            'project_title' => $project->title,
            'project_description' => $project->description,
            'required_skills' => collect($project->required_skills ?? [])->pluck('name')->filter()->values()->all(),
            'category' => $project->category,
            'budget' => [
                'currency' => $project->currency,
                'minimum' => $project->budget_min,
                'maximum' => $project->budget_max,
            ],
            'project_type' => $project->project_type,
            'client_country' => $project->country,
            'client' => [
                'username' => $project->client_username,
                'rating' => $project->client_rating,
                'reviews' => $project->client_reviews,
            ],
            'freelancer_profile' => [
                'name' => $account->display_name ?: $account->username,
                'headline' => $account->profile_description,
                'skills' => $account->skills->pluck('name')->values()->all(),
                'profile_url' => $account->profile_url,
            ],
            'selected_strategy' => [
                'name' => $prepared['strategy']?->name,
                'delivery_days' => $prepared['delivery'],
                'calculated_amount' => $prepared['amount'],
            ],
            'portfolio_links' => $portfolioLinks,
            'constraints' => [
                'proposal_style' => $settings->proposal_style,
                'proposal_max_characters' => $settings->proposal_max_characters,
                'must_only_reference_configured_portfolio_urls' => true,
                'must_not_invent_experience' => true,
            ],
        ];

        return [
            'system_prompt' => $settings->ai_system_prompt ?: $this->settingsService->defaultSystemPrompt(),
            'prompt' => ($settings->ai_proposal_prompt ?: $this->settingsService->defaultProposalPrompt())."\n\n".json_encode($body, JSON_PRETTY_PRINT),
        ];
    }
}

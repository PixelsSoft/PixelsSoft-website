<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerSetting;

class FreelancerSettingsService
{
    public function forAccount(FreelancerAccount $account): FreelancerSetting
    {
        return FreelancerSetting::firstOrCreate(
            ['freelancer_account_id' => $account->id],
            $this->defaults()
        );
    }

    public function defaults(): array
    {
        return [
            'client_id' => config('freelancer.client_id'),
            'oauth_redirect_uri' => config('freelancer.redirect_uri'),
            'api_base_url' => config('freelancer.api.base_url'),
            'environment' => config('freelancer.sandbox') ? 'sandbox' : 'production',
            'api_timeout' => (int) config('freelancer.api.timeout', 30),
            'api_retries' => (int) config('freelancer.api.retries', 2),
            'proposal_mode' => 'template',
            'ai_temperature' => 0.30,
            'ai_max_tokens' => 600,
            'ai_timeout' => 20,
            'ai_retries' => 1,
            'proposal_style' => 'short',
            'proposal_max_characters' => 1400,
            'ai_failure_behavior' => 'fallback_template',
            'fallback_template_enabled' => true,
            'max_bid_submission_seconds' => 60,
            'max_portfolio_links_per_bid' => 3,
            'ai_system_prompt' => $this->defaultSystemPrompt(),
            'ai_proposal_prompt' => $this->defaultProposalPrompt(),
            'notification_preferences' => [
                'account_disconnected' => true,
                'token_expired' => true,
                'automation_paused' => true,
                'bid_submitted' => true,
                'bid_failed' => true,
                'limit_reached' => true,
                'approval_required' => true,
            ],
        ];
    }

    public function defaultSystemPrompt(): string
    {
        return 'You are preparing a professional Freelancer.com proposal for a software developer. Analyze the project requirements carefully. Write a concise, natural and project-specific proposal. Mention only skills and experience that are available in the freelancer profile and configured portfolio. Do not invent experience, technologies, clients, project results or certifications. Do not use excessive marketing language. Focus on understanding the client\'s requirements and explaining how the developer can help.';
    }

    public function defaultProposalPrompt(): string
    {
        return 'Return JSON with keys: proposal, suggested_bid, suggested_delivery_days, selected_portfolio_urls, reasoning, confidence_score. Keep the proposal accurate, concise, and within the configured character limit.';
    }
}

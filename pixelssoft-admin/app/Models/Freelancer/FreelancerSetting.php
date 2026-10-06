<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class FreelancerSetting extends Model
{
    protected $table = 'freelancer_settings';

    protected $fillable = [
        'freelancer_account_id', 'client_id', 'client_secret', 'oauth_redirect_uri', 'api_base_url',
        'environment', 'api_timeout', 'api_retries', 'api_status', 'api_last_request_at',
        'api_last_success_at', 'api_last_failure_at', 'api_last_response_time_ms', 'api_last_error', 'api_rate_limit',
        'proposal_mode', 'ai_provider', 'ai_api_key', 'ai_model', 'ai_base_url', 'ai_temperature',
        'ai_max_tokens', 'ai_timeout', 'ai_retries', 'ai_enabled', 'ai_proposal_enabled',
        'ai_bid_amount_enabled', 'ai_delivery_enabled', 'ai_portfolio_enabled', 'ai_tested_at',
        'ai_status', 'ai_last_error', 'ai_system_prompt', 'ai_proposal_prompt', 'proposal_style',
        'proposal_max_characters', 'ai_failure_behavior', 'fallback_template_enabled',
        'max_bid_submission_seconds', 'max_portfolio_links_per_bid', 'default_strategy_id',
        'default_template_id', 'notification_preferences',
    ];

    protected $hidden = ['client_secret', 'ai_api_key'];

    protected function casts(): array
    {
        return [
            'api_last_request_at' => 'datetime',
            'api_last_success_at' => 'datetime',
            'api_last_failure_at' => 'datetime',
            'api_rate_limit' => 'array',
            'ai_temperature' => 'decimal:2',
            'ai_enabled' => 'boolean',
            'ai_proposal_enabled' => 'boolean',
            'ai_bid_amount_enabled' => 'boolean',
            'ai_delivery_enabled' => 'boolean',
            'ai_portfolio_enabled' => 'boolean',
            'ai_tested_at' => 'datetime',
            'fallback_template_enabled' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function defaultStrategy(): BelongsTo
    {
        return $this->belongsTo(FreelancerStrategy::class, 'default_strategy_id');
    }

    public function defaultTemplate(): BelongsTo
    {
        return $this->belongsTo(FreelancerBidTemplate::class, 'default_template_id');
    }

    public function setClientSecretAttribute(?string $value): void
    {
        $this->attributes['client_secret'] = $value === null || $value === ''
            ? ($this->attributes['client_secret'] ?? null)
            : Crypt::encryptString($value);
    }

    public function setAiApiKeyAttribute(?string $value): void
    {
        $this->attributes['ai_api_key'] = $value === null || $value === ''
            ? ($this->attributes['ai_api_key'] ?? null)
            : Crypt::encryptString($value);
    }

    public function plainClientSecret(): ?string
    {
        return $this->decryptSecret($this->attributes['client_secret'] ?? null);
    }

    public function plainAiApiKey(): ?string
    {
        return $this->decryptSecret($this->attributes['ai_api_key'] ?? null);
    }

    public function maskedClientSecret(): string
    {
        return $this->attributes['client_secret'] ?? null ? '---' : '';
    }

    public function maskedAiApiKey(): string
    {
        return $this->attributes['ai_api_key'] ?? null ? '---' : '';
    }

    private function decryptSecret(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }
}

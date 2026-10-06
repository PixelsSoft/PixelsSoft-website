<?php

namespace App\Models\Freelancer;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class FreelancerAccount extends Model
{
    use HasFactory;
    protected $table = 'freelancer_accounts';

    protected $fillable = [
        'user_id', 'freelancer_user_id', 'username', 'display_name', 'email',
        'country', 'city', 'timezone', 'profile_url', 'hourly_rate', 'rating',
        'reviews_count', 'profile_description', 'raw_profile',
        'access_token', 'refresh_token', 'token_expires_at',
        'is_connected', 'connected_at', 'last_connection_test_at', 'last_connection_test_ok',
        'automation_enabled', 'dry_run', 'automation_mode', 'global_paused',
        'daily_bid_limit', 'hourly_bid_limit', 'monthly_bid_limit', 'bid_delay_seconds',
        'min_skill_match_percent', 'max_project_age_minutes', 'max_bid_count',
        'project_type_filter', 'country_mode', 'country_codes',
        'budget_min', 'budget_max', 'hourly_rate_min', 'hourly_rate_max',
        'min_client_rating', 'min_client_reviews',
        'positive_keywords', 'negative_keywords', 'min_positive_keywords',
        'include_category_ids', 'exclude_category_ids', 'schedule', 'score_weights',
        'last_sync_at', 'last_project_sync_at', 'last_api_success_at', 'last_api_error_at', 'last_api_error',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'raw_profile' => 'array',
            'country_codes' => 'array',
            'positive_keywords' => 'array',
            'negative_keywords' => 'array',
            'include_category_ids' => 'array',
            'exclude_category_ids' => 'array',
            'schedule' => 'array',
            'score_weights' => 'array',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_connection_test_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'last_project_sync_at' => 'datetime',
            'last_api_success_at' => 'datetime',
            'last_api_error_at' => 'datetime',
            'is_connected' => 'boolean',
            'last_connection_test_ok' => 'boolean',
            'automation_enabled' => 'boolean',
            'dry_run' => 'boolean',
            'global_paused' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'rating' => 'decimal:2',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(FreelancerSetting::class, 'freelancer_account_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(FreelancerSkill::class, 'freelancer_account_id');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(FreelancerPortfolio::class, 'freelancer_account_id');
    }

    public function portfolioLinks(): HasMany
    {
        return $this->hasMany(FreelancerPortfolioLink::class, 'freelancer_account_id');
    }

    public function strategies(): HasMany
    {
        return $this->hasMany(FreelancerStrategy::class, 'freelancer_account_id');
    }

    public function bidTemplates(): HasMany
    {
        return $this->hasMany(FreelancerBidTemplate::class, 'freelancer_account_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(FreelancerProject::class, 'freelancer_account_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(FreelancerBid::class, 'freelancer_account_id');
    }

    public function apiLogs(): HasMany
    {
        return $this->hasMany(FreelancerApiLog::class, 'freelancer_account_id');
    }

    public function automationLogs(): HasMany
    {
        return $this->hasMany(FreelancerAutomationLog::class, 'freelancer_account_id');
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value === null || $value === ''
            ? null
            : Crypt::encryptString($value);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value === null || $value === ''
            ? null
            : Crypt::encryptString($value);
    }

    public function plainAccessToken(): ?string
    {
        return $this->decryptToken($this->attributes['access_token'] ?? null);
    }

    public function plainRefreshToken(): ?string
    {
        return $this->decryptToken($this->attributes['refresh_token'] ?? null);
    }

    public function tokenLooksValid(): bool
    {
        if (!$this->is_connected || !$this->plainAccessToken()) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return (bool) $this->plainRefreshToken();
        }

        return true;
    }

    public function tokenStatusLabel(): string
    {
        if (!$this->is_connected) {
            return 'Disconnected';
        }
        if (!$this->plainAccessToken()) {
            return 'Missing token';
        }
        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return $this->plainRefreshToken() ? 'Expired (refresh available)' : 'Expired';
        }

        return 'Connected';
    }

    public function canAutomate(): bool
    {
        return $this->is_connected
            && $this->automation_enabled
            && ! $this->global_paused
            && $this->tokenLooksValid();
    }

    private function decryptToken(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }
}



<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerProject extends Model
{
    use HasFactory;
    protected $table = 'freelancer_projects';

    protected $fillable = [
        'freelancer_account_id', 'freelancer_project_id', 'title', 'description', 'project_url',
        'project_type', 'budget_min', 'budget_max', 'currency', 'hourly_rate', 'duration',
        'country', 'country_code', 'client_id', 'client_username', 'client_rating', 'client_reviews',
        'required_skills', 'category_ids', 'category', 'status', 'bid_count', 'average_bid',
        'posted_at', 'detected_at', 'matching_started_at', 'matching_completed_at', 'portfolio_selected_at',
        'ai_started_at', 'ai_completed_at', 'bid_queued_at', 'bid_started_at', 'bid_submitted_at',
        'target_bid_seconds', 'total_processing_time_ms', 'total_time_to_bid_ms',
        'expires_at', 'matched', 'match_score', 'skill_match_score', 'keyword_score',
        'country_match', 'match_explanation', 'automation_status', 'reject_reason', 'bid_status',
        'matched_strategy_id', 'suggested', 'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'category_ids' => 'array',
            'match_explanation' => 'array',
            'suggested' => 'array',
            'raw_response' => 'array',
            'matched' => 'boolean',
            'country_match' => 'boolean',
            'posted_at' => 'datetime',
            'detected_at' => 'datetime',
            'matching_started_at' => 'datetime',
            'matching_completed_at' => 'datetime',
            'portfolio_selected_at' => 'datetime',
            'ai_started_at' => 'datetime',
            'ai_completed_at' => 'datetime',
            'bid_queued_at' => 'datetime',
            'bid_started_at' => 'datetime',
            'bid_submitted_at' => 'datetime',
            'expires_at' => 'datetime',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'average_bid' => 'decimal:2',
            'match_score' => 'decimal:2',
            'skill_match_score' => 'decimal:2',
            'keyword_score' => 'decimal:2',
            'client_rating' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(FreelancerStrategy::class, 'matched_strategy_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(FreelancerBid::class, 'freelancer_project_id');
    }

    public function automationLogs(): HasMany
    {
        return $this->hasMany(FreelancerAutomationLog::class, 'freelancer_project_id');
    }

    public function getSuggestedAmountAttribute(): ?float
    {
        $value = data_get($this->suggested, 'amount');

        return $value !== null ? (float) $value : null;
    }

    public function getSuggestedDeliveryDaysAttribute(): ?int
    {
        $value = data_get($this->suggested, 'delivery_days');

        return $value !== null ? (int) $value : null;
    }

    public function getSuggestedProposalAttribute(): ?string
    {
        return data_get($this->suggested, 'proposal');
    }

    public function getSelectedPortfolioIdsAttribute(): array
    {
        return data_get($this->suggested, 'portfolio_ids', []) ?: [];
    }

    public function hasSubmittedBid(): bool
    {
        return $this->bids()
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->where('is_dry_run', false)
            ->exists();
    }
}



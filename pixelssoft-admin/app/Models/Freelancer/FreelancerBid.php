<?php

namespace App\Models\Freelancer;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerBid extends Model
{
    use HasFactory;
    protected $table = 'freelancer_bids';

    protected $fillable = [
        'freelancer_account_id', 'freelancer_project_id', 'freelancer_bid_id', 'strategy_id',
        'amount', 'currency', 'delivery_days', 'proposal', 'match_score', 'skill_match_score',
        'keyword_score', 'country_match', 'portfolio_ids', 'status', 'is_dry_run',
        'queued_at', 'processing_started_at', 'ai_processing_time_ms', 'api_response_time_ms',
        'queue_processing_time_ms', 'total_time_to_bid_ms', 'ai_provider', 'ai_model', 'proposal_mode',
        'submitted_at', 'response_data', 'error_message', 'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'portfolio_ids' => 'array',
            'response_data' => 'array',
            'is_dry_run' => 'boolean',
            'country_match' => 'boolean',
            'queued_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'amount' => 'decimal:2',
            'match_score' => 'decimal:2',
            'skill_match_score' => 'decimal:2',
            'keyword_score' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(FreelancerProject::class, 'freelancer_project_id');
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(FreelancerStrategy::class, 'strategy_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}



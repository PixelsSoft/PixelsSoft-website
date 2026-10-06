<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FreelancerStrategy extends Model
{
    use HasFactory;
    protected $table = 'freelancer_strategies';

    protected $fillable = [
        'freelancer_account_id', 'name', 'is_active', 'priority', 'min_skill_match_percent',
        'skill_ids', 'country_codes', 'country_mode', 'budget_min', 'budget_max',
        'min_client_rating', 'max_project_age_minutes', 'max_bid_count', 'bid_delay_seconds',
        'schedule', 'timezone', 'daily_limit', 'bid_amount_mode', 'bid_fixed_amount',
        'bid_percent', 'bid_min', 'bid_max', 'delivery_days', 'delivery_rules',
        'template_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'skill_ids' => 'array',
            'country_codes' => 'array',
            'schedule' => 'array',
            'delivery_rules' => 'array',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'bid_fixed_amount' => 'decimal:2',
            'bid_percent' => 'decimal:2',
            'bid_min' => 'decimal:2',
            'bid_max' => 'decimal:2',
            'min_client_rating' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FreelancerBidTemplate::class, 'template_id');
    }

    public function defaultedBySetting(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerSetting::class, 'freelancer_settings', 'default_strategy_id', 'id');
    }
}



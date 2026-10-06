<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerPortfolio extends Model
{
    protected $table = 'freelancer_portfolios';

    protected $fillable = [
        'freelancer_account_id', 'freelancer_portfolio_id', 'title', 'description',
        'url', 'image_url', 'category', 'skill_ids', 'is_enabled', 'use_for_bidding',
        'status', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'skill_ids' => 'array',
            'is_enabled' => 'boolean',
            'use_for_bidding' => 'boolean',
            'raw' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }
}

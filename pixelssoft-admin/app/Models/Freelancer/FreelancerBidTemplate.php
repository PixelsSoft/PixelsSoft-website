<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerBidTemplate extends Model
{
    use HasFactory;
    protected $table = 'freelancer_bid_templates';

    protected $fillable = [
        'freelancer_account_id', 'strategy_id', 'name', 'content', 'skill_ids', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'skill_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(FreelancerStrategy::class, 'strategy_id');
    }
}



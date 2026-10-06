<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerAutomationLog extends Model
{
    protected $table = 'freelancer_automation_logs';

    protected $fillable = [
        'freelancer_account_id', 'freelancer_project_id', 'freelancer_bid_id',
        'stage', 'level', 'message', 'context', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
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

    public function bid(): BelongsTo
    {
        return $this->belongsTo(FreelancerBid::class, 'freelancer_bid_id');
    }
}

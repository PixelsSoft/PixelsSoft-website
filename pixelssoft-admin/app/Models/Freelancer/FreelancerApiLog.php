<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerApiLog extends Model
{
    protected $table = 'freelancer_api_logs';

    protected $fillable = [
        'freelancer_account_id', 'endpoint', 'method', 'status_code', 'response_time_ms',
        'success', 'error_message', 'related_project_id', 'related_bid_id',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }
}

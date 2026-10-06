<?php

namespace App\Models\Freelancer;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerAuditLog extends Model
{
    protected $table = 'freelancer_audit_logs';

    protected $fillable = [
        'user_id', 'freelancer_account_id', 'action', 'entity_type', 'entity_id',
        'old_value', 'new_value',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }
}

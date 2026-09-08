<?php

namespace App\Models\Accounts;

use App\Models\Pm\Milestone;
use App\Models\Pm\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCommission extends Model
{
    protected $table = 'acc_sales_commissions';

    protected $fillable = [
        'project_id', 'milestone_id', 'user_id', 'percent', 'basis', 'amount',
        'currency', 'status', 'accrued_at', 'paid_at', 'paid_from_account_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
            'accrued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidFromAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'paid_from_account_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}

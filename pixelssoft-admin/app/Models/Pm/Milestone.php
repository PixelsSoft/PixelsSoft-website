<?php

namespace App\Models\Pm;

use App\Models\Accounts\Invoice;
use App\Models\Accounts\PaymentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    protected $table = 'pm_milestones';

    protected $fillable = [
        'project_id', 'title', 'due_date', 'status', 'amount', 'sort_order',
        'payment_account_id', 'released_at', 'released_by', 'settled_at', 'settled_by',
        'platform_fee_amount', 'sales_commission_amount', 'net_amount',
        'billing_currency', 'received_currency', 'received_amount', 'fx_rate',
        'invoice_id', 'portal_milestone_id', 'release_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'released_at' => 'datetime',
            'settled_at' => 'datetime',
            'platform_fee_amount' => 'decimal:2',
            'sales_commission_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'fx_rate' => 'decimal:6',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null || in_array($this->status, ['released', 'awaiting_settlement', 'settled'], true);
    }

    public function isSettled(): bool
    {
        return $this->settled_at !== null || $this->status === 'settled' || $this->invoice_id !== null;
    }

    public function isAwaitingSettlement(): bool
    {
        return $this->isReleased() && !$this->isSettled();
    }
}

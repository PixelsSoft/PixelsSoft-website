<?php

namespace App\Models\Accounts;

use App\Models\Pm\Milestone;
use App\Models\Pm\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    public const TYPE_GROSS_REVENUE = 'gross_revenue';
    public const TYPE_PLATFORM_COMMISSION = 'platform_commission';
    public const TYPE_NET_RECEIPT = 'net_receipt';
    public const TYPE_PROCESSOR_FEE = 'processor_fee';
    public const TYPE_SALES_COMMISSION = 'sales_commission';
    public const TYPE_SALES_PAYOUT = 'sales_payout';

    protected $table = 'acc_ledger_entries';

    protected $fillable = [
        'type', 'amount', 'currency', 'occurred_at', 'project_id', 'milestone_id',
        'payment_account_id', 'invoice_id', 'payment_id', 'sales_commission_id',
        'user_id', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
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

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function salesCommission(): BelongsTo
    {
        return $this->belongsTo(SalesCommission::class, 'sales_commission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function label(): string
    {
        return match ($this->type) {
            self::TYPE_GROSS_REVENUE => 'Project amount',
            self::TYPE_PLATFORM_COMMISSION => 'Platform commission',
            self::TYPE_NET_RECEIPT => 'Net received',
            self::TYPE_PROCESSOR_FEE => 'Processor fee',
            self::TYPE_SALES_COMMISSION => 'Sales commission',
            self::TYPE_SALES_PAYOUT => 'Sales payout',
            default => str_replace('_', ' ', $this->type),
        };
    }
}

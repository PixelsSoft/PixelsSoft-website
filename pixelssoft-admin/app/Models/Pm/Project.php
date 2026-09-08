<?php

namespace App\Models\Pm;

use App\Models\Accounts\LedgerEntry;
use App\Models\Accounts\PaymentAccount;
use App\Models\Accounts\SalesCommission;
use App\Models\Crm\AcquisitionSource;
use App\Models\Crm\Company;
use App\Models\Crm\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'pm_projects';

    protected $fillable = [
        'name', 'code', 'company_id', 'deal_id', 'source_id', 'status', 'priority',
        'start_date', 'due_date', 'budget_hours', 'budget_amount', 'manager_id',
        'sales_person_id', 'payment_account_id', 'currency', 'contract_amount',
        'platform_commission_percent', 'sales_commission_percent', 'sales_commission_basis',
        'portal_contract_id', 'portal_url', 'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'budget_hours' => 'decimal:2',
            'budget_amount' => 'decimal:2',
            'contract_amount' => 'decimal:2',
            'platform_commission_percent' => 'decimal:2',
            'sales_commission_percent' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class, 'source_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'project_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'project_id')->orderBy('sort_order');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'project_id');
    }

    public function salesCommissions(): HasMany
    {
        return $this->hasMany(SalesCommission::class, 'project_id');
    }

    public function money(float $amount): string
    {
        return ($this->currency ?: 'USD') . ' ' . number_format($amount, 2);
    }

    public function financeSummary(): array
    {
        $released = $this->milestones->filter(fn (Milestone $m) => $m->isReleased());
        $gross = (float) $released->sum('amount');
        $fees = (float) $released->sum('platform_fee_amount');
        $sales = (float) $released->sum('sales_commission_amount');
        $net = (float) $released->sum('net_amount');
        $contract = (float) ($this->contract_amount ?: $this->budget_amount ?: 0);

        return [
            'contract' => $contract,
            'released_gross' => $gross,
            'platform_fees' => $fees,
            'sales_commissions' => $sales,
            'net_received' => $net,
            'agency_keep' => round($net - $sales, 2),
            'remaining' => round(max(0, $contract - $gross), 2),
        ];
    }

    public function loggedHours(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }

    public function budgetBurnPercent(): float
    {
        if ($this->budget_hours <= 0) {
            return 0;
        }

        return min(100, round(($this->loggedHours() / $this->budget_hours) * 100, 1));
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return 'PRJ-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

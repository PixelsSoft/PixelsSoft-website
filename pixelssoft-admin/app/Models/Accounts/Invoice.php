<?php

namespace App\Models\Accounts;

use App\Models\Crm\Company;
use App\Models\Crm\Deal;
use App\Models\Pm\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'acc_invoices';

    protected $fillable = [
        'number', 'company_id', 'project_id', 'deal_id', 'status',
        'issue_date', 'due_date', 'subtotal', 'tax', 'total', 'currency', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function paidAmount(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['paid', 'void'], true);
    }

    public function outstanding(): float
    {
        if ($this->isClosed()) {
            return 0;
        }

        $paidSameCurrency = (float) $this->payments
            ->filter(fn (Payment $payment) => !$payment->currency || strtoupper((string) $payment->currency) === strtoupper((string) $this->currency))
            ->sum('amount');

        return round(max(0, (float) $this->total - $paidSameCurrency), 2);
    }

    public function feeAmount(): float
    {
        $fromLines = abs((float) $this->items->filter(fn ($item) => (float) $item->amount < 0)->sum('amount'));
        if ($fromLines > 0) {
            return $fromLines;
        }

        return round(max(0, (float) $this->tax), 2);
    }

    public function displayedFees(): float
    {
        $fees = $this->feeAmount();
        if ($fees > 0) {
            return $fees;
        }

        if ($this->isClosed()) {
            $paid = (float) $this->payments
                ->filter(fn (Payment $payment) => !$payment->currency || strtoupper((string) $payment->currency) === strtoupper((string) $this->currency))
                ->sum('amount');

            return round(max(0, $this->clientAmount() - $paid), 2);
        }

        return 0;
    }

    public function clientAmount(): float
    {
        $positive = (float) $this->items->filter(fn ($item) => (float) $item->amount > 0)->sum('amount');

        return $positive > 0 ? $positive : (float) $this->subtotal;
    }

    public function sourceName(): ?string
    {
        return $this->deal?->acquisitionSource?->name
            ?? $this->project?->source?->name
            ?? $this->deal?->lead?->acquisitionSource?->name
            ?? $this->project?->deal?->acquisitionSource?->name;
    }

    public function portalUrl(): ?string
    {
        return $this->deal?->portal_url
            ?: $this->deal?->lead?->portal_url
            ?: $this->project?->deal?->portal_url
            ?: $this->project?->deal?->lead?->portal_url;
    }

    public function portalContractId(): ?string
    {
        return $this->deal?->portal_contract_id
            ?: $this->deal?->lead?->portal_contract_id
            ?: $this->project?->deal?->portal_contract_id
            ?: $this->project?->deal?->lead?->portal_contract_id;
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'paid' => 'badge-published',
            'partial', 'overdue' => 'badge-unread',
            'void' => 'badge-draft',
            default => 'badge-draft',
        };
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('amount');
        $this->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $this->tax,
        ]);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return 'INV-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

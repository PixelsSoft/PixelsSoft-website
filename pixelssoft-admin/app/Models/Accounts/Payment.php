<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'acc_payments';

    protected $fillable = [
        'invoice_id', 'payment_account_id', 'project_id', 'milestone_id',
        'amount', 'currency', 'method', 'reference', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pm\Project::class, 'project_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pm\Milestone::class, 'milestone_id');
    }
}

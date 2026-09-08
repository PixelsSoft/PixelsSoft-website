<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePayment extends Model
{
    protected $table = 'acc_stripe_payments';

    protected $fillable = [
        'invoice_id', 'payment_id', 'name', 'email', 'phone',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
        'card_brand', 'card_last4', 'card_exp_month', 'card_exp_year', 'card_funding', 'card_country',
        'stripe_customer_id', 'stripe_payment_intent_id', 'stripe_payment_method_id', 'stripe_charge_id',
        'amount', 'currency', 'status', 'ip_address', 'user_agent', 'paid_at',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function formattedAddress(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            strtoupper((string) $this->country),
        ])->filter()->implode(', ');
    }

    public function cardLabel(): string
    {
        if (!$this->card_last4) {
            return '—';
        }

        $brand = $this->card_brand ? ucfirst($this->card_brand) : 'Card';
        $exp = ($this->card_exp_month && $this->card_exp_year)
            ? sprintf('%02d/%d', $this->card_exp_month, $this->card_exp_year)
            : null;

        return trim($brand . ' •••• ' . $this->card_last4 . ($exp ? '  ' . $exp : ''));
    }
}

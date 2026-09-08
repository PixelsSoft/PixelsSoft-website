<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentAccount extends Model
{
    protected $table = 'acc_payment_accounts';

    protected $fillable = [
        'name', 'slug', 'provider', 'currency', 'identifier', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'payment_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_account_id');
    }

    public function receivedTotal(): float
    {
        return (float) $this->ledgerEntries()
            ->where('type', LedgerEntry::TYPE_NET_RECEIPT)
            ->sum('amount');
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'account';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}

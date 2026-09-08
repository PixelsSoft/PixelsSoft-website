<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    protected $table = 'crm_leads';

    protected $fillable = [
        'company_id', 'contact_id', 'title', 'source', 'source_id', 'status', 'score',
        'budget', 'currency', 'owner_id', 'notes', 'contact_message_id',
        'portal_contract_id', 'portal_url',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class, 'source_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'lead_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }
}

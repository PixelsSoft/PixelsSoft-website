<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deal extends Model
{
    protected $table = 'crm_deals';

    protected $fillable = [
        'title', 'company_id', 'contact_id', 'lead_id', 'source_id', 'pipeline_id', 'stage_id',
        'value', 'currency', 'expected_close', 'owner_id', 'sales_person_id',
        'won_at', 'lost_reason', 'notes', 'portal_contract_id', 'portal_url',
    ];

    protected function casts(): array
    {
        return [
            'expected_close' => 'date',
            'won_at' => 'datetime',
            'value' => 'decimal:2',
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class, 'source_id');
    }

    public function project()
    {
        return $this->hasOne(\App\Models\Pm\Project::class, 'deal_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }

    public function isWon(): bool
    {
        return $this->won_at !== null;
    }
}

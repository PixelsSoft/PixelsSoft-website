<?php

namespace App\Models\Pm;

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
        'name', 'code', 'company_id', 'deal_id', 'status', 'priority',
        'start_date', 'due_date', 'budget_hours', 'budget_amount', 'manager_id', 'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'budget_hours' => 'decimal:2',
            'budget_amount' => 'decimal:2',
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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
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

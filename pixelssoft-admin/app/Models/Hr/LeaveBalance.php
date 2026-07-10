<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $table = 'hr_leave_balances';

    protected $fillable = ['employee_id', 'leave_type_id', 'year', 'entitled', 'used'];

    protected function casts(): array
    {
        return ['entitled' => 'decimal:1', 'used' => 'decimal:1'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function remaining(): float
    {
        return (float) $this->entitled - (float) $this->used;
    }
}

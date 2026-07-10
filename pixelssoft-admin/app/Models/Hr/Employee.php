<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'hr_employees';

    protected $fillable = [
        'user_id', 'employee_code', 'department_id', 'position', 'join_date',
        'employment_type', 'salary', 'manager_id', 'status', 'phone', 'address',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrDocument::class, 'employee_id');
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;

        return 'EMP-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrDocument extends Model
{
    protected $table = 'hr_documents';

    protected $fillable = ['employee_id', 'type', 'title', 'file_path', 'expiry_date'];

    protected function casts(): array
    {
        return ['expiry_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

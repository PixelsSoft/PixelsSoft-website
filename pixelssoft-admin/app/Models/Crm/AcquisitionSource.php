<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcquisitionSource extends Model
{
    protected $table = 'crm_acquisition_sources';

    protected $fillable = [
        'name', 'slug', 'type', 'platform_commission_percent',
        'default_sales_commission_percent', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'platform_commission_percent' => 'decimal:2',
            'default_sales_commission_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isFreelancePortal(): bool
    {
        return $this->type === 'freelance_portal';
    }

    public function projects(): HasMany
    {
        return $this->hasMany(\App\Models\Pm\Project::class, 'source_id');
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'source';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}

<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FreelancerCategory extends Model
{
    protected $table = 'freelancer_categories';

    protected $fillable = [
        'freelancer_category_id', 'name', 'path', 'is_active', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'raw' => 'array',
        ];
    }

    public function portfolioLinks(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerPortfolioLink::class, 'freelancer_portfolio_link_category', 'category_id', 'portfolio_link_id');
    }
}
